<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Services\Category;
use App\Services\Product;

final class ProductController extends Controller
{
    public function index(): void
    {
        $q = trim((string) input('q', ''));
        $this->listing([
            'title'       => $q !== '' ? "\"$q\" için arama sonuçları" : 'Tüm Ürünler',
            'description' => $q !== '' ? null : 'Mağazamızdaki tüm ürünleri keşfedin.',
            'category'    => null,
            'filters'     => ['q' => $q],
        ]);
    }

    public function deals(): void
    {
        $this->listing([
            'title'       => 'Kampanyalı Ürünler',
            'description' => 'İndirimli fiyatlarla kaçırılmayacak fırsatlar.',
            'category'    => null,
            'filters'     => ['on_sale' => 1],
        ]);
    }

    public function category(string $slug): void
    {
        $category = Category::findBySlug($slug);
        if (!$category) {
            abort(404);
        }
        $this->listing([
            'title'       => $category['name'],
            'description' => $category['description'],
            'category'    => $category,
            'filters'     => ['category_ids' => Category::descendantIds((int) $category['id'])],
        ]);
    }

    private function listing(array $ctx): void
    {
        $filters = $ctx['filters'];
        $filters['q'] ??= trim((string) input('q', ''));
        $filters['brands'] = array_values(array_filter((array) ($_GET['marka'] ?? []), 'is_string'));
        $filters['min'] = is_numeric(input('min')) ? input('min') : '';
        $filters['max'] = is_numeric(input('max')) ? input('max') : '';
        $filters['in_stock'] = input('stok') === '1';
        $filters['on_sale'] = ($filters['on_sale'] ?? false) || input('indirim') === '1';
        $filters['sort'] = (string) input('sirala', '');

        $perPage = max(4, (int) setting('products_per_page', 12));
        $result = Product::search($filters, (int) input('sayfa', 1), $perPage);

        $category = $ctx['category'];
        $subcategories = [];
        if ($category) {
            $subcategories = array_values(array_filter(Category::all(), static fn ($c) => (int) $c['parent_id'] === (int) $category['id']));
        }

        view('products/index', [
            'pageTitle'     => $ctx['title'],
            'title'         => $ctx['title'],
            'description'   => $ctx['description'],
            'category'      => $category,
            'subcategories' => $subcategories,
            'breadcrumbs'   => $category ? Category::breadcrumbs((int) $category['id']) : [],
            'categories'    => Category::tree(),
            'brands'        => Product::brands($filters['category_ids'] ?? []),
            'filters'       => $filters,
            'result'        => $result,
        ]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            abort(404);
        }
        Database::query('UPDATE products SET views = views + 1 WHERE id = ?', [$product['id']]);

        $reviews = Database::all(
            'SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC',
            [$product['id']]
        );
        $distribution = array_fill(1, 5, 0);
        foreach ($reviews as $r) {
            $distribution[(int) $r['rating']]++;
        }

        $inWishlist = false;
        $canReview = false;
        if (Auth::check()) {
            $inWishlist = (bool) Database::value('SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?', [Auth::id(), $product['id']]);
            $canReview = !(int) Database::value('SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?', [Auth::id(), $product['id']]);
        }

        // Son görüntülenen ürünler (oturum)
        $recent = array_values(array_diff((array) ($_SESSION['recently_viewed'] ?? []), [(int) $product['id']]));
        array_unshift($recent, (int) $product['id']);
        $_SESSION['recently_viewed'] = array_slice($recent, 0, 10);

        view('products/show', [
            'pageTitle'       => $product['meta_title'] ?: $product['name'],
            'metaDescription' => $product['meta_description'] ?: str_limit($product['short_description'], 160),
            'ogImage'         => $product['image'],
            'product'         => $product,
            'images'          => Product::images((int) $product['id']),
            'specs'           => Product::parseSpecs($product['specs']),
            'reviews'         => $reviews,
            'distribution'    => $distribution,
            'related'         => Product::related($product, 4),
            'breadcrumbs'     => Category::breadcrumbs($product['category_id'] ? (int) $product['category_id'] : null),
            'inWishlist'      => $inWishlist,
            'canReview'       => $canReview,
        ]);
    }

    public function review(string $slug): void
    {
        Auth::requireLogin();
        $product = Product::findBySlug($slug);
        if (!$product) {
            abort(404);
        }
        $back = 'urun/' . $product['slug'] . '#yorumlar';
        $data = $this->validate([
            'rating'  => 'required|integer|min_value:1|max_value:5',
            'title'   => 'max:120',
            'comment' => 'required|min:10|max:2000',
        ], ['rating' => 'Puan', 'title' => 'Başlık', 'comment' => 'Yorum'], $back);

        if ((int) Database::value('SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?', [Auth::id(), $product['id']]) > 0) {
            flash('warning', 'Bu ürün için zaten bir değerlendirme yaptınız.');
            redirect($back);
        }

        $autoApprove = setting('review_auto_approve', '0') === '1';
        Database::insert('reviews', [
            'product_id'  => $product['id'],
            'user_id'     => Auth::id(),
            'author_name' => auth()['name'],
            'rating'      => (int) $data['rating'],
            'title'       => $data['title'] ?: null,
            'comment'     => $data['comment'],
            'is_approved' => $autoApprove ? 1 : 0,
            'created_at'  => now(),
        ]);
        flash('success', $autoApprove ? 'Değerlendirmeniz yayınlandı. Teşekkürler!' : 'Değerlendirmeniz alındı, onaylandıktan sonra yayınlanacaktır.');
        redirect($back);
    }

    /** Canlı arama önerileri (JSON) */
    public function suggest(): void
    {
        $q = trim((string) input('q', ''));
        if (mb_strlen($q) < 2) {
            json_response(['items' => []]);
        }
        $items = Product::search(['q' => $q], 1, 6)['items'];
        json_response(['items' => array_map(static fn ($p) => [
            'name'  => $p['name'],
            'url'   => url('urun/' . $p['slug']),
            'image' => media($p['image']),
            'price' => money($p['price']),
            'category' => $p['category_name'],
        ], $items)]);
    }
}

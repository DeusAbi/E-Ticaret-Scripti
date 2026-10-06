<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Upload;
use App\Services\Category;
use App\Services\Product;
use RuntimeException;

final class ProductController extends AdminController
{
    public function index(): void
    {
        $where = ['1 = 1'];
        $params = [];
        if ($q = trim((string) input('q', ''))) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ? OR p.barcode LIKE ?)';
            array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
        }
        if ($cat = (int) input('kategori', 0)) {
            $ids = Category::descendantIds($cat);
            $where[] = 'p.category_id IN (' . Database::placeholders(count($ids)) . ')';
            array_push($params, ...$ids);
        }
        $status = (string) input('durum', '');
        if (isset(Product::STATUSES[$status])) {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }
        $stock = (string) input('stok', '');
        if ($stock === 'out') {
            $where[] = 'p.stock <= 0';
        } elseif ($stock === 'low') {
            $where[] = 'p.stock > 0 AND p.stock <= p.low_stock_threshold';
        }
        $whereSql = implode(' AND ', $where);
        $order = match ((string) input('sirala', '')) {
            'name' => 'p.name ASC', 'price' => 'p.price DESC', 'stock' => 'p.stock ASC', 'sales' => 'p.sales_count DESC',
            default => 'p.id DESC',
        };

        $result = $this->paginate(
            "SELECT COUNT(*) FROM products p WHERE $whereSql",
            "SELECT p.*, c.name AS category_name,
              (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image
             FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE $whereSql ORDER BY $order",
            $params,
            15
        );

        $this->render('products/index', [
            'pageTitle'  => 'Ürünler',
            'result'     => $result,
            'categories' => Category::all(false),
            'counts'     => [
                'all'    => (int) Database::value('SELECT COUNT(*) FROM products'),
                'active' => (int) Database::value("SELECT COUNT(*) FROM products WHERE status = 'active'"),
                'draft'  => (int) Database::value("SELECT COUNT(*) FROM products WHERE status <> 'active'"),
                'out'    => (int) Database::value('SELECT COUNT(*) FROM products WHERE stock <= 0'),
            ],
        ]);
    }

    public function create(): void
    {
        $this->render('products/form', [
            'pageTitle'  => 'Yeni Ürün',
            'product'    => null,
            'images'     => [],
            'categories' => Category::all(false),
        ]);
    }

    public function edit(string $id): void
    {
        $product = $this->find((int) $id);
        $this->render('products/form', [
            'pageTitle'  => 'Ürünü Düzenle',
            'product'    => $product,
            'images'     => Product::images((int) $product['id']),
            'categories' => Category::all(false),
        ]);
    }

    public function store(): void
    {
        $data = $this->validated(null);
        $data['created_at'] = now();
        $id = Database::insert('products', $data);
        $this->handleImages($id);
        flash('success', 'Ürün başarıyla oluşturuldu.');
        redirect(input('after') === 'new' ? 'admin/urunler/yeni' : "admin/urunler/$id/duzenle");
    }

    public function update(string $id): void
    {
        $product = $this->find((int) $id);
        $data = $this->validated((int) $product['id']);
        Database::update('products', $data, 'id = ?', [$product['id']]);
        $this->handleImages((int) $product['id']);
        flash('success', 'Ürün güncellendi.');
        redirect("admin/urunler/{$product['id']}/duzenle");
    }

    private function validated(?int $id): array
    {
        $back = $id ? "admin/urunler/$id/duzenle" : 'admin/urunler/yeni';
        foreach (['price', 'compare_price', 'cost_price'] as $f) {
            if (isset($_POST[$f]) && $_POST[$f] !== '') {
                $_POST[$f] = normalize_decimal_input($_POST[$f]);
            }
        }
        $data = $this->validate([
            'name'                => 'required|max:190',
            'slug'                => 'max:190',
            'category_id'         => 'integer',
            'sku'                 => 'max:100|unique:products,sku' . ($id ? ",$id" : ''),
            'barcode'             => 'max:100',
            'brand'               => 'max:120',
            'short_description'   => 'max:500',
            'description'         => 'max:20000',
            'specs'               => 'max:5000',
            'price'               => 'required|numeric|min_value:0',
            'compare_price'       => 'numeric|min_value:0',
            'cost_price'          => 'numeric|min_value:0',
            'tax_rate'            => 'required|in:0,1,10,20',
            'stock'               => 'required|integer|min_value:0',
            'low_stock_threshold' => 'integer|min_value:0',
            'status'              => 'required|in:' . implode(',', array_keys(Product::STATUSES)),
            'meta_title'          => 'max:190',
            'meta_description'    => 'max:300',
        ], [
            'name' => 'Ürün adı', 'slug' => 'URL', 'category_id' => 'Kategori', 'sku' => 'Stok kodu (SKU)', 'barcode' => 'Barkod',
            'brand' => 'Marka', 'short_description' => 'Kısa açıklama', 'description' => 'Açıklama', 'specs' => 'Özellikler',
            'price' => 'Satış fiyatı', 'compare_price' => 'Eski fiyat', 'cost_price' => 'Maliyet', 'tax_rate' => 'KDV oranı',
            'stock' => 'Stok', 'low_stock_threshold' => 'Kritik stok', 'status' => 'Durum', 'meta_title' => 'SEO başlığı',
            'meta_description' => 'SEO açıklaması',
        ], $back);

        $price = (float) $data['price'];
        $compare = $data['compare_price'] !== null && $data['compare_price'] !== '' ? (float) $data['compare_price'] : null;
        if ($compare !== null && $compare <= $price) {
            $compare = null;
        }
        $slugSource = $data['slug'] ?: $data['name'];

        return [
            'category_id'         => $data['category_id'] ? (int) $data['category_id'] : null,
            'name'                => $data['name'],
            'slug'                => Product::uniqueSlug($slugSource, $id),
            'sku'                 => $data['sku'] ?: null,
            'barcode'             => $data['barcode'] ?: null,
            'brand'               => $data['brand'] ?: null,
            'short_description'   => $data['short_description'] ?: null,
            'description'         => $data['description'] ?: null,
            'specs'               => $data['specs'] ?: null,
            'price'               => $price,
            'compare_price'       => $compare,
            'cost_price'          => $data['cost_price'] !== null && $data['cost_price'] !== '' ? (float) $data['cost_price'] : null,
            'tax_rate'            => (int) $data['tax_rate'],
            'stock'               => (int) $data['stock'],
            'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? 5),
            'status'              => $data['status'],
            'is_featured'         => input('is_featured') === '1' ? 1 : 0,
            'meta_title'          => $data['meta_title'] ?: null,
            'meta_description'    => $data['meta_description'] ?: null,
            'updated_at'          => now(),
        ];
    }

    private function handleImages(int $productId): void
    {
        $files = Upload::normalize($_FILES['images'] ?? null);
        if (!$files) {
            return;
        }
        $order = (int) Database::value('SELECT COALESCE(MAX(sort_order), -1) FROM product_images WHERE product_id = ?', [$productId]);
        $errors = [];
        foreach ($files as $file) {
            try {
                $path = Upload::image($file, 'products');
                Database::insert('product_images', ['product_id' => $productId, 'path' => $path, 'sort_order' => ++$order]);
            } catch (RuntimeException $e) {
                $errors[] = $file['name'] . ': ' . $e->getMessage();
            }
        }
        if ($errors) {
            flash('warning', 'Bazı görseller yüklenemedi: ' . implode(' ', $errors));
        }
    }

    public function deleteImage(string $id): void
    {
        $image = Database::one('SELECT * FROM product_images WHERE id = ?', [(int) $id]);
        if ($image) {
            Database::delete('product_images', 'id = ?', [$image['id']]);
            $this->deleteFileIfUnused($image['path']);
        }
        if (is_ajax()) {
            json_response(['ok' => true]);
        }
        flash('success', 'Görsel silindi.');
        back('admin/urunler');
    }

    public function coverImage(string $id): void
    {
        $image = Database::one('SELECT * FROM product_images WHERE id = ?', [(int) $id]);
        if ($image) {
            Database::query('UPDATE product_images SET sort_order = sort_order + 1 WHERE product_id = ?', [$image['product_id']]);
            Database::update('product_images', ['sort_order' => 0], 'id = ?', [$image['id']]);
            flash('success', 'Kapak görseli güncellendi.');
        }
        back('admin/urunler');
    }

    public function destroy(string $id): void
    {
        $product = $this->find((int) $id);
        $hasOrders = (int) Database::value('SELECT COUNT(*) FROM order_items WHERE product_id = ?', [$product['id']]) > 0;
        if ($hasOrders) {
            // Sipariş geçmişini korumak için ürünü silmek yerine pasife al
            Database::update('products', ['status' => 'passive', 'updated_at' => now()], 'id = ?', [$product['id']]);
            flash('warning', 'Bu ürüne ait siparişler bulunduğu için ürün silinmedi, pasif duruma alındı.');
        } else {
            $images = Product::images((int) $product['id']);
            Database::delete('products', 'id = ?', [$product['id']]);
            foreach ($images as $img) {
                $this->deleteFileIfUnused($img['path']);
            }
            flash('success', 'Ürün silindi.');
        }
        redirect('admin/urunler');
    }

    public function duplicate(string $id): void
    {
        $product = $this->find((int) $id);
        unset($product['id']);
        $product['name'] .= ' (Kopya)';
        $product['slug'] = Product::uniqueSlug($product['name']);
        $product['sku'] = $product['sku'] ? $product['sku'] . '-K' . random_int(10, 99) : null;
        $product['status'] = 'draft';
        $product['views'] = 0;
        $product['sales_count'] = 0;
        $product['created_at'] = $product['updated_at'] = now();
        $newId = Database::insert('products', $product);
        foreach (Product::images((int) $id) as $img) {
            Database::insert('product_images', ['product_id' => $newId, 'path' => $img['path'], 'sort_order' => $img['sort_order']]);
        }
        flash('success', 'Ürün kopyalandı. Kopya taslak olarak kaydedildi.');
        redirect("admin/urunler/$newId/duzenle");
    }

    public function bulk(): void
    {
        $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));
        $action = (string) input('action');
        if (!$ids) {
            flash('warning', 'Lütfen en az bir ürün seçin.');
            back('admin/urunler');
        }
        $in = Database::placeholders(count($ids));
        switch ($action) {
            case 'active':
            case 'draft':
            case 'passive':
                Database::query("UPDATE products SET status = ?, updated_at = ? WHERE id IN ($in)", [$action, now(), ...$ids]);
                flash('success', count($ids) . ' ürünün durumu "' . Product::STATUSES[$action] . '" olarak güncellendi.');
                break;
            case 'feature':
            case 'unfeature':
                Database::query("UPDATE products SET is_featured = ? WHERE id IN ($in)", [$action === 'feature' ? 1 : 0, ...$ids]);
                flash('success', 'Seçili ürünler güncellendi.');
                break;
            default:
                flash('warning', 'Geçersiz işlem.');
        }
        back('admin/urunler');
    }

    /** Kopyalanan ürünler aynı dosyayı paylaşabilir; başka kayıt kullanmıyorsa dosyayı sil */
    private function deleteFileIfUnused(string $path): void
    {
        if ((int) Database::value('SELECT COUNT(*) FROM product_images WHERE path = ?', [$path]) === 0) {
            Upload::delete($path);
        }
    }

    private function find(int $id): array
    {
        $product = Database::one('SELECT * FROM products WHERE id = ?', [$id]);
        if (!$product) {
            abort(404);
        }
        return $product;
    }
}

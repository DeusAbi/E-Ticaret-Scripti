<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Icons;
use App\Core\Upload;
use App\Services\Category;
use App\Services\Product;
use RuntimeException;

final class CategoryController extends AdminController
{
    public function index(): void
    {
        $this->render('categories/index', [
            'pageTitle' => 'Kategoriler',
            'tree'      => Category::tree(false),
            'all'       => Category::all(false),
        ]);
    }

    public function create(): void
    {
        $this->render('categories/form', ['pageTitle' => 'Yeni Kategori', 'category' => null, 'parents' => $this->parents(null), 'icons' => $this->icons()]);
    }

    public function edit(string $id): void
    {
        $category = $this->find((int) $id);
        $this->render('categories/form', ['pageTitle' => 'Kategoriyi Düzenle', 'category' => $category, 'parents' => $this->parents((int) $id), 'icons' => $this->icons()]);
    }

    public function store(): void
    {
        $data = $this->validated(null);
        $data['created_at'] = now();
        $id = Database::insert('categories', $data);
        $this->handleImage($id, null);
        flash('success', 'Kategori oluşturuldu.');
        redirect('admin/kategoriler');
    }

    public function update(string $id): void
    {
        $category = $this->find((int) $id);
        $data = $this->validated((int) $id);
        if ($data['parent_id'] && in_array((int) $data['parent_id'], Category::descendantIds((int) $id), true)) {
            flash('error', 'Bir kategori kendi alt kategorisinin altına taşınamaz.');
            redirect("admin/kategoriler/$id/duzenle");
        }
        Database::update('categories', $data, 'id = ?', [$id]);
        $this->handleImage((int) $id, $category['image']);
        flash('success', 'Kategori güncellendi.');
        redirect('admin/kategoriler');
    }

    public function destroy(string $id): void
    {
        $category = $this->find((int) $id);
        Database::update('categories', ['parent_id' => $category['parent_id']], 'parent_id = ?', [$category['id']]);
        Database::delete('categories', 'id = ?', [$category['id']]);
        Upload::delete($category['image']);
        flash('success', 'Kategori silindi. Bu kategorideki ürünler kategorisiz olarak işaretlendi.');
        redirect('admin/kategoriler');
    }

    private function validated(?int $id): array
    {
        $data = $this->validate([
            'name'        => 'required|max:120',
            'slug'        => 'max:190',
            'parent_id'   => 'integer',
            'description' => 'max:1000',
            'icon'        => 'max:40',
            'sort_order'  => 'integer',
        ], ['name' => 'Kategori adı', 'slug' => 'URL', 'parent_id' => 'Üst kategori', 'description' => 'Açıklama', 'icon' => 'İkon', 'sort_order' => 'Sıra'],
            $id ? "admin/kategoriler/$id/duzenle" : 'admin/kategoriler/yeni');

        return [
            'name'        => $data['name'],
            'slug'        => Product::uniqueSlug($data['slug'] ?: $data['name'], $id, 'categories'),
            'parent_id'   => $data['parent_id'] && (int) $data['parent_id'] !== $id ? (int) $data['parent_id'] : null,
            'description' => $data['description'] ?: null,
            'icon'        => isset(Icons::PATHS[$data['icon'] ?? '']) ? $data['icon'] : null,
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'is_active'   => input('is_active') === '1' ? 1 : 0,
        ];
    }

    private function handleImage(int $id, ?string $old): void
    {
        if (input('remove_image') === '1' && $old) {
            Upload::delete($old);
            Database::update('categories', ['image' => null], 'id = ?', [$id]);
        }
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $path = Upload::image($_FILES['image'], 'categories');
                Database::update('categories', ['image' => $path], 'id = ?', [$id]);
                Upload::delete($old);
            } catch (RuntimeException $e) {
                flash('warning', 'Görsel yüklenemedi: ' . $e->getMessage());
            }
        }
    }

    private function parents(?int $exceptId): array
    {
        $all = Category::all(false);
        if ($exceptId) {
            $exclude = Category::descendantIds($exceptId);
            $all = array_filter($all, static fn ($c) => !in_array((int) $c['id'], $exclude, true));
        }
        return array_values($all);
    }

    private function icons(): array
    {
        return ['smartphone', 'laptop', 'headphones', 'watch', 'camera', 'monitor', 'tablet', 'keyboard', 'speaker', 'gamepad',
            'shirt', 'shoe', 'bag', 'backpack', 'glasses', 'gem', 'sofa', 'lamp', 'coffee', 'plant', 'dumbbell', 'bike', 'tent',
            'bottle', 'droplet', 'sparkles', 'book', 'gift', 'package', 'tag', 'store', 'heart', 'zap', 'award'];
    }

    private function find(int $id): array
    {
        $c = Database::one('SELECT * FROM categories WHERE id = ?', [$id]);
        if (!$c) {
            abort(404);
        }
        return $c;
    }
}

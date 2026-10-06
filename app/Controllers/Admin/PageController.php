<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\Product;

final class PageController extends AdminController
{
    public function index(): void
    {
        $this->render('pages/index', ['pageTitle' => 'Sayfalar', 'pages' => Database::all('SELECT * FROM pages ORDER BY sort_order, id')]);
    }

    public function create(): void
    {
        $this->render('pages/form', ['pageTitle' => 'Yeni Sayfa', 'page' => null]);
    }

    public function edit(string $id): void
    {
        $this->render('pages/form', ['pageTitle' => 'Sayfayı Düzenle', 'page' => $this->find((int) $id)]);
    }

    public function store(): void
    {
        $data = $this->validated(null);
        $data['created_at'] = now();
        Database::insert('pages', $data);
        flash('success', 'Sayfa oluşturuldu.');
        redirect('admin/sayfalar');
    }

    public function update(string $id): void
    {
        $this->find((int) $id);
        Database::update('pages', $this->validated((int) $id), 'id = ?', [(int) $id]);
        flash('success', 'Sayfa güncellendi.');
        redirect('admin/sayfalar');
    }

    public function destroy(string $id): void
    {
        Database::delete('pages', 'id = ?', [(int) $id]);
        flash('success', 'Sayfa silindi.');
        redirect('admin/sayfalar');
    }

    private function validated(?int $id): array
    {
        $data = $this->validate([
            'title'      => 'required|max:190',
            'slug'       => 'max:190',
            'content'    => 'max:50000',
            'sort_order' => 'integer',
        ], ['title' => 'Başlık', 'slug' => 'URL', 'content' => 'İçerik', 'sort_order' => 'Sıra'],
            $id ? "admin/sayfalar/$id/duzenle" : 'admin/sayfalar/yeni');

        return [
            'title'          => $data['title'],
            'slug'           => Product::uniqueSlug($data['slug'] ?: $data['title'], $id, 'pages'),
            'content'        => $data['content'] ?: null,
            'sort_order'     => (int) ($data['sort_order'] ?? 0),
            'show_in_footer' => input('show_in_footer') === '1' ? 1 : 0,
            'is_active'      => input('is_active') === '1' ? 1 : 0,
            'updated_at'     => now(),
        ];
    }

    private function find(int $id): array
    {
        $p = Database::one('SELECT * FROM pages WHERE id = ?', [$id]);
        if (!$p) {
            abort(404);
        }
        return $p;
    }
}

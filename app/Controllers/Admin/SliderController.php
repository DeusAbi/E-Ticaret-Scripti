<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Upload;
use RuntimeException;

final class SliderController extends AdminController
{
    public function index(): void
    {
        $this->render('sliders/index', ['pageTitle' => 'Ana Sayfa Slider', 'sliders' => Database::all('SELECT * FROM sliders ORDER BY sort_order, id')]);
    }

    public function create(): void
    {
        $this->render('sliders/form', ['pageTitle' => 'Yeni Slayt', 'slider' => null]);
    }

    public function edit(string $id): void
    {
        $this->render('sliders/form', ['pageTitle' => 'Slaytı Düzenle', 'slider' => $this->find((int) $id)]);
    }

    public function store(): void
    {
        $data = $this->validated(null);
        $data['created_at'] = now();
        $id = Database::insert('sliders', $data);
        $this->handleImage($id, null);
        flash('success', 'Slayt eklendi.');
        redirect('admin/slider');
    }

    public function update(string $id): void
    {
        $slider = $this->find((int) $id);
        Database::update('sliders', $this->validated((int) $id), 'id = ?', [$slider['id']]);
        $this->handleImage((int) $slider['id'], $slider['image']);
        flash('success', 'Slayt güncellendi.');
        redirect('admin/slider');
    }

    public function destroy(string $id): void
    {
        $slider = $this->find((int) $id);
        Database::delete('sliders', 'id = ?', [$slider['id']]);
        Upload::delete($slider['image']);
        flash('success', 'Slayt silindi.');
        redirect('admin/slider');
    }

    private function validated(?int $id): array
    {
        $data = $this->validate([
            'eyebrow'     => 'max:60',
            'title'       => 'required|max:120',
            'subtitle'    => 'max:300',
            'button_text' => 'max:40',
            'button_link' => 'max:255',
            'bg_color'    => 'required|color',
            'theme'       => 'required|in:dark,light',
            'sort_order'  => 'integer',
        ], ['eyebrow' => 'Üst başlık', 'title' => 'Başlık', 'subtitle' => 'Alt metin', 'button_text' => 'Buton metni',
            'button_link' => 'Buton bağlantısı', 'bg_color' => 'Arka plan rengi', 'theme' => 'Yazı rengi', 'sort_order' => 'Sıra'],
            $id ? "admin/slider/$id/duzenle" : 'admin/slider/yeni');

        return [
            'eyebrow'     => $data['eyebrow'] ?: null,
            'title'       => $data['title'],
            'subtitle'    => $data['subtitle'] ?: null,
            'button_text' => $data['button_text'] ?: null,
            'button_link' => $data['button_link'] ?: null,
            'bg_color'    => $data['bg_color'],
            'theme'       => $data['theme'],
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'is_active'   => input('is_active') === '1' ? 1 : 0,
        ];
    }

    private function handleImage(int $id, ?string $old): void
    {
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $path = Upload::image($_FILES['image'], 'sliders');
                Database::update('sliders', ['image' => $path], 'id = ?', [$id]);
                Upload::delete($old);
            } catch (RuntimeException $e) {
                flash('warning', 'Görsel yüklenemedi: ' . $e->getMessage());
            }
        }
    }

    private function find(int $id): array
    {
        $s = Database::one('SELECT * FROM sliders WHERE id = ?', [$id]);
        if (!$s) {
            abort(404);
        }
        return $s;
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Installer;
use App\Core\Settings;
use App\Core\Upload;
use RuntimeException;

final class SettingController extends AdminController
{
    private const CHECKBOXES = ['card_enabled', 'transfer_enabled', 'cod_enabled', 'review_auto_approve'];
    private const DECIMALS = ['shipping_fee', 'free_shipping_threshold', 'cod_fee'];

    public function index(): void
    {
        $this->render('settings', ['pageTitle' => 'Mağaza Ayarları', 'settings' => Settings::all() + Installer::defaultSettings()]);
    }

    public function update(): void
    {
        foreach (self::DECIMALS as $f) {
            if (isset($_POST[$f]) && $_POST[$f] !== '') {
                $_POST[$f] = normalize_decimal_input($_POST[$f]);
            }
        }
        $this->validate([
            'store_name'              => 'required|max:80',
            'store_email'             => 'required|email',
            'store_phone'             => 'max:40',
            'primary_color'           => 'required|color',
            'shipping_fee'            => 'required|numeric|min_value:0',
            'free_shipping_threshold' => 'numeric|min_value:0',
            'cod_fee'                 => 'numeric|min_value:0',
            'products_per_page'       => 'required|integer|min_value:4|max_value:60',
        ], [
            'store_name' => 'Mağaza adı', 'store_email' => 'E-posta', 'store_phone' => 'Telefon', 'primary_color' => 'Ana renk',
            'shipping_fee' => 'Kargo ücreti', 'free_shipping_threshold' => 'Ücretsiz kargo limiti', 'cod_fee' => 'Kapıda ödeme ücreti',
            'products_per_page' => 'Sayfa başına ürün',
        ], 'admin/ayarlar');

        $allowed = array_keys(Installer::defaultSettings());
        foreach ($allowed as $key) {
            if (in_array($key, self::CHECKBOXES, true)) {
                Settings::set($key, input($key) === '1' ? '1' : '0');
            } elseif (array_key_exists($key, $_POST)) {
                Settings::set($key, trim((string) $_POST[$key]));
            }
        }

        if (!empty($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $old = (string) setting('store_logo');
                Settings::set('store_logo', Upload::image($_FILES['logo'], 'brand'));
                Upload::delete($old);
            } catch (RuntimeException $e) {
                flash('warning', 'Logo yüklenemedi: ' . $e->getMessage());
            }
        } elseif (input('remove_logo') === '1') {
            Upload::delete((string) setting('store_logo'));
            Settings::set('store_logo', null);
        }

        flash('success', 'Ayarlar kaydedildi.');
        redirect('admin/ayarlar' . (input('tab') ? '#' . preg_replace('/[^a-z-]/', '', (string) input('tab')) : ''));
    }
}

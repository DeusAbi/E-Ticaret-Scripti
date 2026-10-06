<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class CouponController extends AdminController
{
    public function index(): void
    {
        $this->render('coupons/index', [
            'pageTitle' => 'Kuponlar',
            'coupons'   => Database::all('SELECT * FROM coupons ORDER BY id DESC'),
        ]);
    }

    public function create(): void
    {
        $this->render('coupons/form', ['pageTitle' => 'Yeni Kupon', 'coupon' => null]);
    }

    public function edit(string $id): void
    {
        $this->render('coupons/form', ['pageTitle' => 'Kuponu Düzenle', 'coupon' => $this->find((int) $id)]);
    }

    public function store(): void
    {
        $data = $this->validated(null);
        $data['created_at'] = now();
        $data['used_count'] = 0;
        Database::insert('coupons', $data);
        flash('success', 'Kupon oluşturuldu.');
        redirect('admin/kuponlar');
    }

    public function update(string $id): void
    {
        $this->find((int) $id);
        Database::update('coupons', $this->validated((int) $id), 'id = ?', [(int) $id]);
        flash('success', 'Kupon güncellendi.');
        redirect('admin/kuponlar');
    }

    public function destroy(string $id): void
    {
        Database::delete('coupons', 'id = ?', [(int) $id]);
        flash('success', 'Kupon silindi.');
        redirect('admin/kuponlar');
    }

    private function validated(?int $id): array
    {
        $_POST['code'] = strtoupper(preg_replace('/\s+/', '', (string) ($_POST['code'] ?? '')));
        foreach (['value', 'min_total'] as $f) {
            if (isset($_POST[$f]) && $_POST[$f] !== '') {
                $_POST[$f] = normalize_decimal_input($_POST[$f]);
            }
        }
        $data = $this->validate([
            'code'       => 'required|min:3|max:40|unique:coupons,code' . ($id ? ",$id" : ''),
            'type'       => 'required|in:percent,fixed',
            'value'      => 'required|numeric|min_value:0.01',
            'min_total'  => 'numeric|min_value:0',
            'max_uses'   => 'integer|min_value:0',
            'starts_at'  => 'date',
            'expires_at' => 'date',
        ], ['code' => 'Kupon kodu', 'type' => 'İndirim tipi', 'value' => 'İndirim değeri', 'min_total' => 'Minimum sepet tutarı',
            'max_uses' => 'Kullanım limiti', 'starts_at' => 'Başlangıç tarihi', 'expires_at' => 'Bitiş tarihi'],
            $id ? "admin/kuponlar/$id/duzenle" : 'admin/kuponlar/yeni');

        if ($data['type'] === 'percent' && (float) $data['value'] > 100) {
            with_errors(['value' => 'Yüzde indirim en fazla 100 olabilir.']);
            redirect($id ? "admin/kuponlar/$id/duzenle" : 'admin/kuponlar/yeni');
        }

        return [
            'code'       => $data['code'],
            'type'       => $data['type'],
            'value'      => (float) $data['value'],
            'min_total'  => (float) ($data['min_total'] ?? 0),
            'max_uses'   => (int) ($data['max_uses'] ?? 0),
            'starts_at'  => $data['starts_at'] ? date('Y-m-d H:i:s', strtotime($data['starts_at'])) : null,
            'expires_at' => $data['expires_at'] ? date('Y-m-d 23:59:59', strtotime($data['expires_at'])) : null,
            'is_active'  => input('is_active') === '1' ? 1 : 0,
        ];
    }

    private function find(int $id): array
    {
        $c = Database::one('SELECT * FROM coupons WHERE id = ?', [$id]);
        if (!$c) {
            abort(404);
        }
        return $c;
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;

final class CustomerController extends AdminController
{
    public function index(): void
    {
        $where = ['1 = 1'];
        $params = [];
        if ($q = trim((string) input('q', ''))) {
            $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            array_push($params, "%$q%", "%$q%", "%$q%");
        }
        $role = (string) input('rol', '');
        if (in_array($role, ['customer', 'admin'], true)) {
            $where[] = 'u.role = ?';
            $params[] = $role;
        }
        $whereSql = implode(' AND ', $where);
        $this->render('customers/index', [
            'pageTitle' => 'Müşteriler',
            'result'    => $this->paginate(
                "SELECT COUNT(*) FROM users u WHERE $whereSql",
                "SELECT u.*,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
                   (SELECT COALESCE(SUM(total),0) FROM orders o WHERE o.user_id = u.id AND o.status NOT IN ('cancelled','refunded')) AS spent
                 FROM users u WHERE $whereSql ORDER BY u.id DESC",
                $params
            ),
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->find((int) $id);
        $this->render('customers/show', [
            'pageTitle' => $user['name'],
            'user'      => $user,
            'orders'    => Database::all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [$user['id']]),
            'addresses' => Database::all('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC', [$user['id']]),
            'spent'     => (float) Database::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id = ? AND status NOT IN ('cancelled','refunded')", [$user['id']]),
        ]);
    }

    public function toggle(string $id): void
    {
        $user = $this->find((int) $id);
        if ((int) $user['id'] === Auth::id()) {
            flash('error', 'Kendi hesabınızı askıya alamazsınız.');
            back('admin/musteriler');
        }
        Database::update('users', ['is_active' => (int) $user['is_active'] ? 0 : 1, 'updated_at' => now()], 'id = ?', [$user['id']]);
        flash('success', (int) $user['is_active'] ? 'Hesap askıya alındı.' : 'Hesap yeniden aktifleştirildi.');
        back('admin/musteriler');
    }

    public function role(string $id): void
    {
        $user = $this->find((int) $id);
        if ((int) $user['id'] === Auth::id()) {
            flash('error', 'Kendi yetkinizi değiştiremezsiniz.');
            back('admin/musteriler');
        }
        $role = $user['role'] === 'admin' ? 'customer' : 'admin';
        Database::update('users', ['role' => $role, 'updated_at' => now()], 'id = ?', [$user['id']]);
        flash('success', $role === 'admin' ? 'Kullanıcıya yönetici yetkisi verildi.' : 'Yönetici yetkisi kaldırıldı.');
        back('admin/musteriler');
    }

    private function find(int $id): array
    {
        $u = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
        if (!$u) {
            abort(404);
        }
        return $u;
    }
}

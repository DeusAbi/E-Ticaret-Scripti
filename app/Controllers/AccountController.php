<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Services\Order;
use App\Services\Product;

final class AccountController extends Controller
{
    public function __construct()
    {
        if (current_path() !== '/favori') {
            Auth::requireLogin();
        }
    }

    public function dashboard(): void
    {
        $uid = Auth::id();
        view('account/dashboard', [
            'pageTitle'    => 'Hesabım',
            'recentOrders' => Database::all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5', [$uid]),
            'stats'        => [
                'orders'    => (int) Database::value('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$uid]),
                'active'    => (int) Database::value("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending','processing','shipped')", [$uid]),
                'wishlist'  => (int) Database::value('SELECT COUNT(*) FROM wishlists WHERE user_id = ?', [$uid]),
                'addresses' => (int) Database::value('SELECT COUNT(*) FROM addresses WHERE user_id = ?', [$uid]),
                'spent'     => (float) Database::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id = ? AND status NOT IN ('cancelled','refunded')", [$uid]),
            ],
        ]);
    }

    public function orders(): void
    {
        $status = (string) input('durum', '');
        $sql = 'SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count FROM orders o WHERE o.user_id = ?';
        $params = [Auth::id()];
        if (isset(Order::STATUSES[$status])) {
            $sql .= ' AND o.status = ?';
            $params[] = $status;
        }
        $orders = Database::all($sql . ' ORDER BY o.id DESC', $params);
        $thumbs = [];
        if ($orders) {
            $ids = array_column($orders, 'id');
            foreach (Database::all('SELECT order_id, image, product_name FROM order_items WHERE order_id IN (' . Database::placeholders(count($ids)) . ') ORDER BY id', $ids) as $row) {
                $thumbs[$row['order_id']][] = $row;
            }
        }
        view('account/orders', ['pageTitle' => 'Siparişlerim', 'orders' => $orders, 'thumbs' => $thumbs, 'status' => $status]);
    }

    public function order(string $orderNo): void
    {
        $order = $this->findOrder($orderNo);
        view('account/order', [
            'pageTitle' => 'Sipariş #' . $order['order_no'],
            'order'     => $order,
            'items'     => Order::items((int) $order['id']),
            'history'   => Order::history((int) $order['id']),
            'banks'     => bank_accounts(),
        ]);
    }

    public function cancelOrder(string $orderNo): void
    {
        $order = $this->findOrder($orderNo);
        if (!in_array($order['status'], ['pending', 'processing'], true)) {
            flash('error', 'Bu sipariş artık iptal edilemez. Lütfen müşteri hizmetleri ile iletişime geçin.');
            redirect('hesabim/siparis/' . $orderNo);
        }
        Order::changeStatus($order, 'cancelled', 'Müşteri tarafından iptal edildi.', Auth::id());
        if ($order['payment_status'] === 'paid') {
            Database::update('orders', ['payment_status' => 'refunded'], 'id = ?', [$order['id']]);
        }
        flash('success', 'Siparişiniz iptal edildi.' . ($order['payment_status'] === 'paid' ? ' Ödemeniz 3-10 iş günü içinde iade edilecektir.' : ''));
        redirect('hesabim/siparis/' . $orderNo);
    }

    private function findOrder(string $orderNo): array
    {
        $order = Database::one('SELECT * FROM orders WHERE order_no = ? AND user_id = ?', [$orderNo, Auth::id()]);
        if (!$order) {
            abort(404);
        }
        return $order;
    }

    public function addresses(): void
    {
        $edit = null;
        if ($id = (int) input('duzenle', 0)) {
            $edit = Database::one('SELECT * FROM addresses WHERE id = ? AND user_id = ?', [$id, Auth::id()]);
        }
        view('account/addresses', [
            'pageTitle' => 'Adreslerim',
            'addresses' => Database::all('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC', [Auth::id()]),
            'edit'      => $edit,
        ]);
    }

    public function saveAddress(): void
    {
        $data = $this->validate([
            'title'       => 'required|max:40',
            'full_name'   => 'required|max:120',
            'phone'       => 'required|phone',
            'city'        => 'required|max:60',
            'district'    => 'required|max:60',
            'address'     => 'required|min:10|max:500',
            'postal_code' => 'max:10',
        ], [
            'title' => 'Adres başlığı', 'full_name' => 'Ad soyad', 'phone' => 'Telefon', 'city' => 'İl',
            'district' => 'İlçe', 'address' => 'Adres', 'postal_code' => 'Posta kodu',
        ], 'hesabim/adresler' . (input('id') ? '?duzenle=' . (int) input('id') : '') . '#adres-formu');

        $id = (int) input('id', 0);
        if ($id) {
            Database::update('addresses', $data, 'id = ? AND user_id = ?', [$id, Auth::id()]);
            flash('success', 'Adresiniz güncellendi.');
        } else {
            $hasAny = (int) Database::value('SELECT COUNT(*) FROM addresses WHERE user_id = ?', [Auth::id()]) > 0;
            Database::insert('addresses', $data + ['user_id' => Auth::id(), 'is_default' => $hasAny ? 0 : 1, 'created_at' => now()]);
            flash('success', 'Yeni adresiniz eklendi.');
        }
        $redirect = safe_redirect_path((string) input('redirect', ''), '/hesabim/adresler');
        redirect(ltrim($redirect, '/'));
    }

    public function deleteAddress(string $id): void
    {
        $address = Database::one('SELECT * FROM addresses WHERE id = ? AND user_id = ?', [(int) $id, Auth::id()]);
        if ($address) {
            Database::delete('addresses', 'id = ?', [$address['id']]);
            if ((int) $address['is_default']) {
                $next = Database::value('SELECT id FROM addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1', [Auth::id()]);
                if ($next) {
                    Database::update('addresses', ['is_default' => 1], 'id = ?', [$next]);
                }
            }
            flash('success', 'Adres silindi.');
        }
        redirect('hesabim/adresler');
    }

    public function defaultAddress(string $id): void
    {
        $address = Database::one('SELECT id FROM addresses WHERE id = ? AND user_id = ?', [(int) $id, Auth::id()]);
        if ($address) {
            Database::update('addresses', ['is_default' => 0], 'user_id = ?', [Auth::id()]);
            Database::update('addresses', ['is_default' => 1], 'id = ?', [$address['id']]);
            flash('success', 'Varsayılan adresiniz güncellendi.');
        }
        redirect('hesabim/adresler');
    }

    public function profile(): void
    {
        view('account/profile', ['pageTitle' => 'Profil Bilgilerim', 'user' => auth()]);
    }

    public function updateProfile(): void
    {
        $_POST['email'] = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $data = $this->validate([
            'name'  => 'required|min:3|max:120',
            'email' => 'required|email|max:190|unique:users,email,' . Auth::id(),
            'phone' => 'phone',
        ], ['name' => 'Ad soyad', 'email' => 'E-posta adresi', 'phone' => 'Telefon'], 'hesabim/profil');

        Database::update('users', [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?: null,
            'newsletter' => input('newsletter') === '1' ? 1 : 0,
            'updated_at' => now(),
        ], 'id = ?', [Auth::id()]);
        flash('success', 'Profil bilgileriniz güncellendi.');
        redirect('hesabim/profil');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|max:72|password',
            'password_confirmation' => 'required|same:password',
        ], ['current_password' => 'Mevcut şifre', 'password' => 'Yeni şifre', 'password_confirmation' => 'Yeni şifre tekrarı'], 'hesabim/profil#sifre');

        if (!password_verify((string) $_POST['current_password'], auth()['password'])) {
            with_errors(['current_password' => 'Mevcut şifreniz hatalı.']);
            redirect('hesabim/profil#sifre');
        }
        Database::update('users', ['password' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT), 'updated_at' => now()], 'id = ?', [Auth::id()]);
        flash('success', 'Şifreniz başarıyla değiştirildi.');
        redirect('hesabim/profil');
    }

    public function wishlist(): void
    {
        $items = Database::all(
            'SELECT ' . Product::CARD_SELECT . " FROM wishlists w JOIN products p ON p.id = w.product_id
             LEFT JOIN categories c ON c.id = p.category_id WHERE w.user_id = ? AND p.status = 'active' ORDER BY w.id DESC",
            [Auth::id()]
        );
        view('account/wishlist', ['pageTitle' => 'Favorilerim', 'items' => $items]);
    }

    public function toggleWishlist(): void
    {
        if (!Auth::check()) {
            if (is_ajax()) {
                json_response(['ok' => false, 'login' => url('giris'), 'message' => 'Favorilere eklemek için giriş yapın.'], 401);
            }
            Auth::requireLogin();
        }
        $productId = (int) input('product_id');
        if (!Product::findActive($productId)) {
            abort(404);
        }
        $exists = (int) Database::value('SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?', [Auth::id(), $productId]);
        if ($exists) {
            Database::delete('wishlists', 'user_id = ? AND product_id = ?', [Auth::id(), $productId]);
            $message = 'Ürün favorilerinizden çıkarıldı.';
        } else {
            Database::insert('wishlists', ['user_id' => Auth::id(), 'product_id' => $productId, 'created_at' => now()]);
            $message = 'Ürün favorilerinize eklendi.';
        }
        if (is_ajax()) {
            json_response(['ok' => true, 'active' => !$exists, 'message' => $message]);
        }
        flash('success', $message);
        back('hesabim/favoriler');
    }
}

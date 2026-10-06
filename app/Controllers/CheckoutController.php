<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Payment\Gateway;
use App\Services\Cart;
use App\Services\Order;
use RuntimeException;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $summary = Cart::summary();
        if (!$summary['items']) {
            flash('warning', 'Ödeme adımına geçmek için sepetinize ürün ekleyin.');
            redirect('sepet');
        }
        foreach ($summary['warnings'] as $w) {
            Session::flashNow('warning', $w);
        }

        view('checkout/index', [
            'pageTitle' => 'Güvenli Ödeme',
            'summary'   => $summary,
            'addresses' => Database::all('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC', [Auth::id()]),
            'methods'   => self::enabledMethods(),
            'codFee'    => (float) setting('cod_fee', 0),
        ]);
    }

    public static function enabledMethods(): array
    {
        $methods = [];
        foreach (Order::PAYMENT_METHODS as $key => $m) {
            if (setting($key . '_enabled', '1') === '1') {
                $methods[$key] = $m;
            }
        }
        return $methods;
    }

    public function place(): void
    {
        Auth::requireLogin();
        $user = auth();
        $methods = self::enabledMethods();

        $rules = [
            'address_id'     => 'required',
            'payment_method' => 'required|in:' . implode(',', array_keys($methods)),
            'invoice_type'   => 'required|in:individual,corporate',
            'note'           => 'max:500',
            'agreement'      => 'required',
        ];
        if (input('address_id') === 'new') {
            $rules += [
                'full_name' => 'required|max:120',
                'phone'     => 'required|phone',
                'city'      => 'required|max:60',
                'district'  => 'required|max:60',
                'address'   => 'required|min:10|max:500',
                'postal_code' => 'max:10',
            ];
        }
        if (input('invoice_type') === 'corporate') {
            $rules += ['invoice_company' => 'required|max:190', 'invoice_tax_office' => 'required|max:120', 'invoice_tax_no' => 'required|min:10|max:11'];
        }
        $data = $this->validate($rules, [
            'address_id' => 'Teslimat adresi', 'payment_method' => 'Ödeme yöntemi', 'invoice_type' => 'Fatura tipi',
            'note' => 'Sipariş notu', 'agreement' => 'Sözleşme onayı', 'full_name' => 'Ad soyad', 'phone' => 'Telefon',
            'city' => 'İl', 'district' => 'İlçe', 'address' => 'Adres', 'postal_code' => 'Posta kodu',
            'invoice_company' => 'Firma adı', 'invoice_tax_office' => 'Vergi dairesi', 'invoice_tax_no' => 'Vergi numarası',
        ], 'odeme');

        // Teslimat adresi
        if ($data['address_id'] === 'new') {
            $address = [
                'full_name' => $data['full_name'], 'phone' => $data['phone'], 'city' => $data['city'],
                'district' => $data['district'], 'address' => $data['address'], 'postal_code' => $data['postal_code'],
            ];
            if (input('save_address') === '1') {
                $hasAny = (int) Database::value('SELECT COUNT(*) FROM addresses WHERE user_id = ?', [Auth::id()]) > 0;
                Database::insert('addresses', $address + [
                    'user_id' => Auth::id(), 'title' => trim((string) input('address_title')) ?: 'Adresim',
                    'is_default' => $hasAny ? 0 : 1, 'created_at' => now(),
                ]);
            }
        } else {
            $address = Database::one('SELECT * FROM addresses WHERE id = ? AND user_id = ?', [(int) $data['address_id'], Auth::id()]);
            if (!$address) {
                flash('error', 'Lütfen geçerli bir teslimat adresi seçin.');
                redirect('odeme');
            }
        }

        $method = $data['payment_method'];
        $card = null;
        if ($method === 'card') {
            $card = (array) ($_POST['card'] ?? []);
            if ($cardError = Gateway::validateCard($card)) {
                with_errors(['card' => $cardError]);
                flash('error', $cardError);
                redirect('odeme');
            }
        }

        $summary = Cart::summary(true, $method);
        if (!$summary['items']) {
            flash('warning', 'Sepetiniz boş.');
            redirect('sepet');
        }

        $customer = [
            'name'               => $address['full_name'],
            'email'              => $user['email'],
            'phone'              => $address['phone'],
            'city'               => $address['city'],
            'district'           => $address['district'],
            'address'            => $address['address'],
            'postal'             => $address['postal_code'] ?? '',
            'invoice_type'       => $data['invoice_type'],
            'invoice_company'    => $data['invoice_company'] ?? '',
            'invoice_tax_office' => $data['invoice_tax_office'] ?? '',
            'invoice_tax_no'     => $data['invoice_tax_no'] ?? '',
            'note'               => $data['note'] ?? '',
        ];

        try {
            $order = Order::createFromCart($summary, $customer, $method, Auth::id());
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('sepet');
        }

        if ($method === 'card') {
            $result = Gateway::make()->charge($order, $card);
            if (!$result->success) {
                Order::changeStatus($order, 'cancelled', 'Ödeme başarısız: ' . $result->message, null, false);
                Database::update('orders', ['payment_status' => 'failed'], 'id = ?', [$order['id']]);
                flash('error', $result->message ?? 'Ödeme alınamadı.');
                redirect('odeme');
            }
            Database::update('orders', ['payment_status' => 'paid', 'payment_ref' => $result->reference, 'status' => 'processing', 'updated_at' => now()], 'id = ?', [$order['id']]);
            Order::addHistory((int) $order['id'], 'processing', 'Ödeme alındı, sipariş hazırlanıyor.');
            $order = Database::one('SELECT * FROM orders WHERE id = ?', [$order['id']]);
        }

        Cart::clear();
        Order::sendConfirmationMail($order);
        Session::set('last_order', $order['order_no']);
        redirect('siparis-tamamlandi/' . $order['order_no']);
    }

    public function success(string $orderNo): void
    {
        Auth::requireLogin();
        $order = Database::one('SELECT * FROM orders WHERE order_no = ? AND user_id = ?', [$orderNo, Auth::id()]);
        if (!$order) {
            abort(404);
        }
        view('checkout/success', [
            'pageTitle' => 'Siparişiniz Alındı',
            'order'     => $order,
            'items'     => Order::items((int) $order['id']),
            'banks'     => bank_accounts(),
        ]);
    }
}

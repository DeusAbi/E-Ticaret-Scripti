<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Mailer;
use RuntimeException;

final class Order
{
    public const STATUSES = [
        'pending'    => ['label' => 'Onay Bekliyor',   'color' => 'warning', 'icon' => 'clock'],
        'processing' => ['label' => 'Hazırlanıyor',    'color' => 'info',    'icon' => 'package'],
        'shipped'    => ['label' => 'Kargoya Verildi', 'color' => 'primary', 'icon' => 'truck'],
        'delivered'  => ['label' => 'Teslim Edildi',   'color' => 'success', 'icon' => 'check-circle'],
        'cancelled'  => ['label' => 'İptal Edildi',    'color' => 'danger',  'icon' => 'x'],
        'refunded'   => ['label' => 'İade Edildi',     'color' => 'muted',   'icon' => 'rotate'],
    ];

    public const PAYMENT_METHODS = [
        'card'     => ['label' => 'Kredi / Banka Kartı', 'icon' => 'card'],
        'transfer' => ['label' => 'Havale / EFT',        'icon' => 'banknote'],
        'cod'      => ['label' => 'Kapıda Ödeme',        'icon' => 'wallet'],
    ];

    public const PAYMENT_STATUSES = [
        'pending'  => ['label' => 'Ödeme Bekleniyor', 'color' => 'warning'],
        'paid'     => ['label' => 'Ödendi',           'color' => 'success'],
        'failed'   => ['label' => 'Başarısız',        'color' => 'danger'],
        'refunded' => ['label' => 'İade Edildi',      'color' => 'muted'],
    ];

    /** Müşteriye gösterilen sipariş adımları */
    public const TIMELINE = ['pending', 'processing', 'shipped', 'delivered'];

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status]['label'] ?? $status;
    }

    public static function generateNumber(): string
    {
        do {
            $no = 'SP' . date('ymd') . random_int(10000, 99999);
        } while ((int) Database::value('SELECT COUNT(*) FROM orders WHERE order_no = ?', [$no]) > 0);
        return $no;
    }

    /**
     * Sepetten sipariş oluşturur. Stok, kupon ve toplamlar işlem (transaction) içinde kontrol edilir.
     *
     * @param array $customer name, email, phone, city, district, address, postal, invoice_*, note
     * @return array Oluşturulan sipariş
     */
    public static function createFromCart(array $summary, array $customer, string $paymentMethod, ?int $userId): array
    {
        if (!$summary['items']) {
            throw new RuntimeException('Sepetiniz boş.');
        }

        return Database::transaction(static function () use ($summary, $customer, $paymentMethod, $userId) {
            // Stokları kilitle / kontrol et ve düş
            foreach ($summary['items'] as $item) {
                $affected = Database::query(
                    "UPDATE products SET stock = stock - ?, sales_count = sales_count + ? WHERE id = ? AND stock >= ? AND status = 'active'",
                    [$item['qty'], $item['qty'], $item['id'], $item['qty']]
                )->rowCount();
                if ($affected !== 1) {
                    throw new RuntimeException("\"{$item['product']['name']}\" için yeterli stok kalmadı. Lütfen sepetinizi kontrol edin.");
                }
            }

            if ($summary['coupon']) {
                $c = $summary['coupon'];
                $ok = Database::query(
                    'UPDATE coupons SET used_count = used_count + 1 WHERE id = ? AND (max_uses = 0 OR used_count < max_uses)',
                    [$c['id']]
                )->rowCount();
                if ($ok !== 1) {
                    throw new RuntimeException('Kupon kullanım limiti dolduğu için sipariş oluşturulamadı.');
                }
            }

            $now = now();
            $orderId = Database::insert('orders', [
                'order_no'           => self::generateNumber(),
                'user_id'            => $userId,
                'status'             => 'pending',
                'payment_method'     => $paymentMethod,
                'payment_status'     => 'pending',
                'subtotal'           => $summary['subtotal'],
                'discount'           => $summary['discount'],
                'shipping_fee'       => $summary['shipping'],
                'payment_fee'        => $summary['payment_fee'],
                'tax_total'          => $summary['tax_included'],
                'total'              => $summary['total'],
                'coupon_code'        => $summary['coupon']['code'] ?? null,
                'customer_name'      => $customer['name'],
                'customer_email'     => $customer['email'],
                'customer_phone'     => $customer['phone'],
                'ship_city'          => $customer['city'],
                'ship_district'      => $customer['district'],
                'ship_address'       => $customer['address'],
                'ship_postal'        => $customer['postal'] ?: null,
                'invoice_type'       => $customer['invoice_type'] ?? 'individual',
                'invoice_company'    => $customer['invoice_company'] ?: null,
                'invoice_tax_office' => $customer['invoice_tax_office'] ?: null,
                'invoice_tax_no'     => $customer['invoice_tax_no'] ?: null,
                'note'               => $customer['note'] ?: null,
                'ip_address'         => client_ip(),
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            foreach ($summary['items'] as $item) {
                Database::insert('order_items', [
                    'order_id'     => $orderId,
                    'product_id'   => $item['id'],
                    'product_name' => $item['product']['name'],
                    'sku'          => $item['product']['sku'],
                    'image'        => $item['product']['image'],
                    'price'        => $item['price'],
                    'quantity'     => $item['qty'],
                    'total'        => $item['total'],
                ]);
            }

            self::addHistory($orderId, 'pending', 'Sipariş oluşturuldu.');
            return Database::one('SELECT * FROM orders WHERE id = ?', [$orderId]);
        });
    }

    public static function addHistory(int $orderId, string $status, ?string $note = null, ?int $by = null): void
    {
        Database::insert('order_history', [
            'order_id'   => $orderId,
            'status'     => $status,
            'note'       => $note,
            'created_by' => $by,
            'created_at' => now(),
        ]);
    }

    /**
     * Sipariş durumunu değiştirir; iptal/iade durumunda stokları geri yükler.
     */
    public static function changeStatus(array $order, string $status, ?string $note = null, ?int $by = null, bool $notify = true): void
    {
        if (!isset(self::STATUSES[$status]) || $order['status'] === $status) {
            return;
        }
        Database::transaction(static function () use ($order, $status, $note, $by) {
            $wasReleased = in_array($order['status'], ['cancelled', 'refunded'], true);
            $releases = in_array($status, ['cancelled', 'refunded'], true);

            if ($releases && !$wasReleased) {
                self::restock((int) $order['id'], +1);
                if ($order['coupon_code']) {
                    Database::query('UPDATE coupons SET used_count = used_count - 1 WHERE code = ? AND used_count > 0', [$order['coupon_code']]);
                }
            } elseif (!$releases && $wasReleased) {
                self::restock((int) $order['id'], -1);
            }

            $data = ['status' => $status, 'updated_at' => now()];
            if ($status === 'refunded' && $order['payment_status'] === 'paid') {
                $data['payment_status'] = 'refunded';
            }
            if ($status === 'delivered' && $order['payment_method'] === 'cod') {
                $data['payment_status'] = 'paid';
            }
            Database::update('orders', $data, 'id = ?', [$order['id']]);
            self::addHistory((int) $order['id'], $status, $note, $by);
        });

        if ($notify) {
            $fresh = Database::one('SELECT * FROM orders WHERE id = ?', [$order['id']]);
            self::sendStatusMail($fresh);
        }
    }

    /** $direction: +1 stoğa geri ekle, -1 stoktan düş */
    private static function restock(int $orderId, int $direction): void
    {
        foreach (Database::all('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL', [$orderId]) as $row) {
            $qty = (int) $row['quantity'] * $direction;
            Database::query(
                'UPDATE products SET stock = stock + ?, sales_count = CASE WHEN sales_count - ? < 0 THEN 0 ELSE sales_count - ? END WHERE id = ?',
                [$qty, $qty, $qty, $row['product_id']]
            );
        }
    }

    public static function items(int $orderId): array
    {
        return Database::all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    public static function history(int $orderId): array
    {
        return Database::all('SELECT * FROM order_history WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    public static function sendConfirmationMail(array $order): void
    {
        $items = self::items((int) $order['id']);
        $body = \App\Core\View::partial('partials/email-order', ['order' => $order, 'items' => $items]);
        Mailer::sendTemplate(
            $order['customer_email'],
            'Siparişiniz alındı - #' . $order['order_no'],
            'Siparişiniz için teşekkürler!',
            $body
        );
        $adminEmail = (string) setting('store_email');
        if ($adminEmail !== '') {
            Mailer::sendTemplate($adminEmail, 'Yeni sipariş - #' . $order['order_no'], 'Yeni bir sipariş aldınız', $body);
        }
    }

    public static function sendStatusMail(array $order): void
    {
        $label = self::statusLabel($order['status']);
        $body = '<p>Merhaba ' . e($order['customer_name']) . ',</p>'
            . '<p><strong>#' . e($order['order_no']) . '</strong> numaralı siparişinizin durumu <strong>' . e($label) . '</strong> olarak güncellendi.</p>';
        if ($order['status'] === 'shipped' && $order['tracking_no']) {
            $body .= '<p>Kargo firması: <strong>' . e($order['cargo_company']) . '</strong><br>Takip numarası: <strong>' . e($order['tracking_no']) . '</strong></p>';
        }
        $body .= '<p><a href="' . e(absolute_url('hesabim/siparis/' . $order['order_no'])) . '">Siparişinizi görüntüleyin</a></p>';
        Mailer::sendTemplate($order['customer_email'], "Sipariş durumu: $label - #" . $order['order_no'], 'Sipariş durumunuz güncellendi', $body);
    }
}

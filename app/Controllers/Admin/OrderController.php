<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Services\Order;

final class OrderController extends AdminController
{
    public function index(): void
    {
        $where = ['1 = 1'];
        $params = [];
        if ($q = trim((string) input('q', ''))) {
            $where[] = '(o.order_no LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ?)';
            array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
        }
        $status = (string) input('durum', '');
        if (isset(Order::STATUSES[$status])) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }
        $payment = (string) input('odeme', '');
        if (isset(Order::PAYMENT_METHODS[$payment])) {
            $where[] = 'o.payment_method = ?';
            $params[] = $payment;
        }
        if ($from = (string) input('baslangic', '')) {
            $where[] = 'o.created_at >= ?';
            $params[] = $from . ' 00:00:00';
        }
        if ($to = (string) input('bitis', '')) {
            $where[] = 'o.created_at <= ?';
            $params[] = $to . ' 23:59:59';
        }
        $whereSql = implode(' AND ', $where);

        if (input('export') === 'csv') {
            $this->exportCsv($whereSql, $params);
        }

        $counts = ['' => (int) Database::value('SELECT COUNT(*) FROM orders')];
        foreach (Database::all('SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status') as $r) {
            $counts[$r['status']] = (int) $r['cnt'];
        }

        $this->render('orders/index', [
            'pageTitle' => 'Siparişler',
            'counts'    => $counts,
            'result'    => $this->paginate(
                "SELECT COUNT(*) FROM orders o WHERE $whereSql",
                "SELECT o.*, (SELECT SUM(quantity) FROM order_items i WHERE i.order_id = o.id) AS item_count FROM orders o WHERE $whereSql ORDER BY o.id DESC",
                $params
            ),
        ]);
    }

    private function exportCsv(string $whereSql, array $params): never
    {
        $rows = Database::all("SELECT * FROM orders o WHERE $whereSql ORDER BY o.id DESC", $params);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="siparisler-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Sipariş No', 'Tarih', 'Müşteri', 'E-posta', 'Telefon', 'İl', 'İlçe', 'Durum', 'Ödeme Yöntemi', 'Ödeme Durumu', 'Ara Toplam', 'İndirim', 'Kargo', 'Toplam'], ';');
        foreach ($rows as $o) {
            fputcsv($out, [
                $o['order_no'], $o['created_at'], $o['customer_name'], $o['customer_email'], $o['customer_phone'], $o['ship_city'], $o['ship_district'],
                Order::statusLabel($o['status']), Order::PAYMENT_METHODS[$o['payment_method']]['label'] ?? $o['payment_method'],
                Order::PAYMENT_STATUSES[$o['payment_status']]['label'] ?? $o['payment_status'],
                money($o['subtotal'], false), money($o['discount'], false), money($o['shipping_fee'], false), money($o['total'], false),
            ], ';');
        }
        fclose($out);
        exit;
    }

    public function show(string $id): void
    {
        $order = $this->find((int) $id);
        $this->render('orders/show', [
            'pageTitle' => 'Sipariş #' . $order['order_no'],
            'order'     => $order,
            'items'     => Order::items((int) $order['id']),
            'history'   => Database::all('SELECT h.*, u.name AS user_name FROM order_history h LEFT JOIN users u ON u.id = h.created_by WHERE h.order_id = ? ORDER BY h.id DESC', [$order['id']]),
            'customer'  => $order['user_id'] ? Database::one('SELECT id, name, email, created_at, (SELECT COUNT(*) FROM orders WHERE user_id = users.id) AS order_count FROM users WHERE id = ?', [$order['user_id']]) : null,
            'cargos'    => array_filter(array_map('trim', explode(',', (string) setting('cargo_companies')))),
        ]);
    }

    public function invoice(string $id): void
    {
        $order = $this->find((int) $id);
        view('admin/orders/invoice', ['order' => $order, 'items' => Order::items((int) $order['id'])], null);
    }

    public function updateStatus(string $id): void
    {
        $order = $this->find((int) $id);
        $data = $this->validate([
            'status'        => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'cargo_company' => 'max:100',
            'tracking_no'   => 'max:100',
            'note'          => 'max:500',
        ], ['status' => 'Durum', 'cargo_company' => 'Kargo firması', 'tracking_no' => 'Takip numarası', 'note' => 'Not'], "admin/siparisler/$id");

        if ($data['status'] === 'shipped' && (!$data['tracking_no'] || !$data['cargo_company'])) {
            with_errors(['tracking_no' => 'Kargoya verildi durumu için kargo firması ve takip numarası zorunludur.']);
            redirect("admin/siparisler/$id");
        }

        Database::update('orders', [
            'cargo_company' => $data['cargo_company'] ?: null,
            'tracking_no'   => $data['tracking_no'] ?: null,
            'updated_at'    => now(),
        ], 'id = ?', [$order['id']]);
        $order = $this->find((int) $id);

        if ($data['status'] !== $order['status']) {
            Order::changeStatus($order, $data['status'], $data['note'] ?: null, Auth::id(), input('notify') === '1');
            flash('success', 'Sipariş durumu "' . Order::statusLabel($data['status']) . '" olarak güncellendi.');
        } else {
            if ($data['note']) {
                Order::addHistory((int) $order['id'], $order['status'], $data['note'], Auth::id());
            }
            flash('success', 'Sipariş bilgileri kaydedildi.');
        }
        redirect("admin/siparisler/$id");
    }

    public function updatePayment(string $id): void
    {
        $order = $this->find((int) $id);
        $status = (string) input('payment_status');
        if (!isset(Order::PAYMENT_STATUSES[$status])) {
            abort(422);
        }
        Database::update('orders', ['payment_status' => $status, 'updated_at' => now()], 'id = ?', [$order['id']]);
        Order::addHistory((int) $order['id'], $order['status'], 'Ödeme durumu: ' . Order::PAYMENT_STATUSES[$status]['label'], Auth::id());
        if ($status === 'paid' && $order['status'] === 'pending') {
            Order::changeStatus($order, 'processing', 'Ödeme onaylandı.', Auth::id());
        }
        flash('success', 'Ödeme durumu güncellendi.');
        redirect("admin/siparisler/$id");
    }

    public function updateNote(string $id): void
    {
        $order = $this->find((int) $id);
        Database::update('orders', ['admin_note' => mb_substr(trim((string) input('admin_note', '')), 0, 2000) ?: null], 'id = ?', [$order['id']]);
        flash('success', 'Sipariş notu kaydedildi.');
        redirect("admin/siparisler/$id");
    }

    private function find(int $id): array
    {
        $order = Database::one('SELECT * FROM orders WHERE id = ?', [$id]);
        if (!$order) {
            abort(404);
        }
        return $order;
    }
}

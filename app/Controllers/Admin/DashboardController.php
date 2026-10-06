<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $valid = "status NOT IN ('cancelled','refunded')";
        $today = date('Y-m-d 00:00:00');
        $monthStart = date('Y-m-01 00:00:00');
        $last30 = date('Y-m-d 00:00:00', strtotime('-29 days'));
        $prev30 = date('Y-m-d 00:00:00', strtotime('-59 days'));

        $revenue30 = (float) Database::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid AND created_at >= ?", [$last30]);
        $revenuePrev = (float) Database::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid AND created_at >= ? AND created_at < ?", [$prev30, $last30]);
        $orders30 = (int) Database::value("SELECT COUNT(*) FROM orders WHERE created_at >= ?", [$last30]);

        $stats = [
            'today_revenue'  => (float) Database::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid AND created_at >= ?", [$today]),
            'today_orders'   => (int) Database::value('SELECT COUNT(*) FROM orders WHERE created_at >= ?', [$today]),
            'month_revenue'  => (float) Database::value("SELECT COALESCE(SUM(total),0) FROM orders WHERE $valid AND created_at >= ?", [$monthStart]),
            'revenue30'      => $revenue30,
            'revenue_change' => $revenuePrev > 0 ? round(($revenue30 - $revenuePrev) / $revenuePrev * 100, 1) : null,
            'orders30'       => $orders30,
            'avg_order'      => $orders30 ? $revenue30 / max(1, (int) Database::value("SELECT COUNT(*) FROM orders WHERE $valid AND created_at >= ?", [$last30])) : 0,
            'pending'        => (int) Database::value("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'processing'     => (int) Database::value("SELECT COUNT(*) FROM orders WHERE status = 'processing'"),
            'customers'      => (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'customer'"),
            'new_customers'  => (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= ?", [$last30]),
            'products'       => (int) Database::value("SELECT COUNT(*) FROM products WHERE status = 'active'"),
            'out_of_stock'   => (int) Database::value("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock <= 0"),
            'pending_reviews'=> (int) Database::value('SELECT COUNT(*) FROM reviews WHERE is_approved = 0'),
            'unread_messages'=> (int) Database::value('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
        ];

        // Son 30 günlük satış grafiği
        $rows = Database::all(
            "SELECT SUBSTR(created_at, 1, 10) AS day, COALESCE(SUM(total),0) AS revenue, COUNT(*) AS orders
             FROM orders WHERE $valid AND created_at >= ? GROUP BY SUBSTR(created_at, 1, 10)",
            [$last30]
        );
        $byDay = array_column($rows, null, 'day');
        $chart = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chart[] = ['day' => $d, 'revenue' => (float) ($byDay[$d]['revenue'] ?? 0), 'orders' => (int) ($byDay[$d]['orders'] ?? 0)];
        }

        $statusCounts = [];
        foreach (Database::all('SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status') as $r) {
            $statusCounts[$r['status']] = (int) $r['cnt'];
        }

        $this->render('dashboard', [
            'pageTitle'    => 'Kontrol Paneli',
            'stats'        => $stats,
            'chart'        => $chart,
            'statusCounts' => $statusCounts,
            'recentOrders' => Database::all('SELECT * FROM orders ORDER BY id DESC LIMIT 8'),
            'lowStock'     => Database::all(
                "SELECT p.id, p.name, p.sku, p.stock, p.low_stock_threshold,
                 (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image
                 FROM products p WHERE p.status = 'active' AND p.stock <= p.low_stock_threshold ORDER BY p.stock ASC LIMIT 6"
            ),
            'topProducts'  => Database::all(
                "SELECT oi.product_name, oi.image, SUM(oi.quantity) AS qty, SUM(oi.total) AS revenue
                 FROM order_items oi JOIN orders o ON o.id = oi.order_id
                 WHERE o.status NOT IN ('cancelled','refunded') AND o.created_at >= ?
                 GROUP BY oi.product_name, oi.image ORDER BY qty DESC LIMIT 5",
                [$last30]
            ),
            'defaultAdmin' => auth()['email'] === 'admin@example.com' && password_verify('admin123', auth()['password']),
        ]);
    }
}

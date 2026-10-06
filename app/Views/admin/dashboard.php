<?php
use App\Services\Order;
/** @var array $stats @var array $chart @var array $statusCounts @var array $recentOrders @var array $lowStock @var array $topProducts */
$max = max(1, max(array_column($chart, 'revenue')));
// Okunaklı eksen: 4 eşit aralık, her biri 1-2-2,5-5 x 10^n katı
$rawStep = $max / 4;
$mag = pow(10, floor(log10($rawStep)));
$step = $mag * (($rawStep / $mag) <= 1 ? 1 : (($rawStep / $mag) <= 2 ? 2 : (($rawStep / $mag) <= 2.5 ? 2.5 : (($rawStep / $mag) <= 5 ? 5 : 10))));
$niceMax = $step * 4;
$totalOrders = max(1, array_sum($statusCounts));
$kfmt = static fn ($v) => $v >= 1000 ? rtrim(rtrim(number_format($v / 1000, 1, ',', '.'), '0'), ',') . 'B' : number_format($v, 0, ',', '.');
?>
<?= partial('admin/partials-head', [
    'title' => 'Hoş geldiniz, ' . explode(' ', auth()['name'])[0] . ' 👋',
    'sub'   => 'Mağazanızın ' . date_tr(date('Y-m-d'), false) . ' itibarıyla genel durumu.',
    'actions' => '<a href="' . e(url('admin/siparisler?export=csv')) . '" class="btn btn-outline btn-sm">' . icon('file', '', 16) . ' Siparişleri Dışa Aktar</a>',
]) ?>

<?php if ($defaultAdmin): ?>
    <div class="alert-banner"><?= icon('alert', '', 20) ?> <span><strong>Güvenlik uyarısı:</strong> Varsayılan yönetici şifresini kullanıyorsunuz. Lütfen şifrenizi hemen değiştirin.</span> <a href="<?= e(url('hesabim/profil#sifre')) ?>" class="btn btn-sm btn-dark">Şifreyi Değiştir</a></div>
<?php endif; ?>

<div class="kpis">
    <div class="kpi">
        <div class="top"><span class="label">Bugünkü Satış</span><span class="ic"><?= icon('wallet', '', 20) ?></span></div>
        <div class="value"><?= e(money($stats['today_revenue'])) ?></div>
        <div class="sub"><?= $stats['today_orders'] ?> sipariş bugün</div>
    </div>
    <div class="kpi" style="--kpi-bg:#ecfdf3;--kpi-fg:#16a34a">
        <div class="top"><span class="label">Son 30 Gün Ciro</span><span class="ic"><?= icon('trending', '', 20) ?></span></div>
        <div class="value"><?= e(money($stats['revenue30'])) ?></div>
        <div class="sub">
            <?php if ($stats['revenue_change'] !== null): ?>
                <span class="<?= $stats['revenue_change'] >= 0 ? 'trend-up' : 'trend-down' ?>"><?= $stats['revenue_change'] >= 0 ? '▲' : '▼' ?> %<?= number_format(abs($stats['revenue_change']), 1, ',', '') ?></span> önceki 30 güne göre
            <?php else: ?>Ort. sepet: <?= e(money($stats['avg_order'])) ?><?php endif; ?>
        </div>
    </div>
    <div class="kpi" style="--kpi-bg:#eff5ff;--kpi-fg:#2563eb">
        <div class="top"><span class="label">Bekleyen Siparişler</span><span class="ic"><?= icon('package', '', 20) ?></span></div>
        <div class="value"><?= $stats['pending'] + $stats['processing'] ?></div>
        <div class="sub"><?= $stats['pending'] ?> onay bekliyor · <?= $stats['processing'] ?> hazırlanıyor</div>
    </div>
    <div class="kpi" style="--kpi-bg:#f3f0ff;--kpi-fg:#7c3aed">
        <div class="top"><span class="label">Müşteriler</span><span class="ic"><?= icon('users', '', 20) ?></span></div>
        <div class="value"><?= $stats['customers'] ?></div>
        <div class="sub"><span class="trend-up">+<?= $stats['new_customers'] ?></span> son 30 günde</div>
    </div>
</div>

<div class="dash-grid">
    <div class="panel">
        <div class="panel-head"><h3><?= icon('chart', '', 18) ?> Satış Grafiği <span class="small text-muted" style="font-weight:500">· Son 30 gün</span></h3><strong style="color:var(--ink)"><?= e(money($stats['revenue30'])) ?></strong></div>
        <div class="panel-body">
            <div class="chart">
                <div class="chart-grid">
                    <?php for ($i = 4; $i >= 0; $i--): ?><span data-label="<?= e($kfmt($niceMax * $i / 4)) ?>"></span><?php endfor; ?>
                </div>
                <div class="chart-bars">
                    <?php foreach ($chart as $i => $d): ?>
                        <div class="chart-bar<?= $i === count($chart) - 1 ? ' is-today' : '' ?>">
                            <i style="height:<?= round($d['revenue'] / $niceMax * 100, 2) ?>%"></i>
                            <span class="tip"><strong><?= e(date('d.m', strtotime($d['day']))) ?></strong><br><?= e(money($d['revenue'])) ?> · <?= $d['orders'] ?> sipariş</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-x">
                    <?php foreach ($chart as $i => $d): if ($i % 5 === 0 || $i === count($chart) - 1): ?><span><?= e(date('d.m', strtotime($d['day']))) ?></span><?php endif; endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3><?= icon('layers', '', 18) ?> Sipariş Durumları</h3></div>
        <div class="panel-body">
            <div class="status-list">
                <?php foreach (Order::STATUSES as $key => $s): $cnt = $statusCounts[$key] ?? 0; ?>
                    <a class="status-row" href="<?= e(url('admin/siparisler?durum=' . $key)) ?>">
                        <span><?= e($s['label']) ?></span><strong style="color:var(--ink)"><?= $cnt ?></strong>
                        <span class="bar"><i class="c-<?= e($s['color']) ?>" style="width:<?= round($cnt / $totalOrders * 100) ?>%"></i></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <hr>
            <div class="kv">
                <div><span>Aktif ürün</span><strong><?= $stats['products'] ?></strong></div>
                <div><span>Stokta olmayan</span><strong class="<?= $stats['out_of_stock'] ? 'text-danger' : '' ?>"><?= $stats['out_of_stock'] ?></strong></div>
                <div><span>Onay bekleyen yorum</span><strong><a href="<?= e(url('admin/yorumlar?durum=bekleyen')) ?>"><?= $stats['pending_reviews'] ?></a></strong></div>
                <div><span>Okunmamış mesaj</span><strong><a href="<?= e(url('admin/mesajlar')) ?>"><?= $stats['unread_messages'] ?></a></strong></div>
            </div>
        </div>
    </div>
</div>

<div class="dash-grid">
    <div class="panel">
        <div class="panel-head"><h3><?= icon('package', '', 18) ?> Son Siparişler</h3><a href="<?= e(url('admin/siparisler')) ?>" class="btn btn-ghost btn-sm">Tümü <?= icon('arrow-right', '', 15) ?></a></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Sipariş</th><th>Müşteri</th><th>Durum</th><th>Ödeme</th><th class="text-right">Tutar</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><a class="link-strong" href="<?= e(url('admin/siparisler/' . $o['id'])) ?>">#<?= e($o['order_no']) ?></a><div class="small text-muted"><?= e(time_ago($o['created_at'])) ?></div></td>
                        <td><?= e($o['customer_name']) ?><div class="small text-muted"><?= e($o['ship_city']) ?></div></td>
                        <td><?= order_status_badge($o['status']) ?></td>
                        <td><?= payment_status_badge($o['payment_status']) ?></td>
                        <td class="text-right num fw-600"><?= e(money($o['total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentOrders): ?><tr class="empty-row"><td colspan="5">Henüz sipariş yok.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('award', '', 18) ?> En Çok Satanlar</h3><span class="small text-muted">30 gün</span></div>
            <ul class="mini-list">
                <?php foreach ($topProducts as $tp): ?>
                    <li><img src="<?= e(media($tp['image'])) ?>" alt=""><div><div class="name"><?= e(str_limit($tp['product_name'], 34)) ?></div><div class="meta"><?= (int) $tp['qty'] ?> adet satıldı</div></div><div class="right fw-600"><?= e(money($tp['revenue'])) ?></div></li>
                <?php endforeach; ?>
                <?php if (!$topProducts): ?><li class="text-muted">Veri yok.</li><?php endif; ?>
            </ul>
        </div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('alert', '', 18) ?> Kritik Stok</h3><a href="<?= e(url('admin/urunler?stok=low')) ?>" class="btn btn-ghost btn-sm">Tümü</a></div>
            <ul class="mini-list">
                <?php foreach ($lowStock as $ls): ?>
                    <li><img src="<?= e(media($ls['image'])) ?>" alt=""><div><a class="name" href="<?= e(url('admin/urunler/' . $ls['id'] . '/duzenle')) ?>"><?= e(str_limit($ls['name'], 34)) ?></a><div class="meta"><?= e($ls['sku']) ?></div></div><div class="right"><?= (int) $ls['stock'] <= 0 ? badge('Tükendi', 'danger') : badge($ls['stock'] . ' adet', 'warning') ?></div></li>
                <?php endforeach; ?>
                <?php if (!$lowStock): ?><li class="text-muted">Tüm ürünlerin stoğu yeterli. 🎉</li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>

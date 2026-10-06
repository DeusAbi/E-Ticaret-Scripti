<?php ob_start(); $u = auth(); ?>
<div class="welcome">
    <h2>Merhaba, <?= e(explode(' ', $u['name'])[0]) ?> 👋</h2>
    <p>Hesabınızdan siparişlerinizi takip edebilir, adres ve profil bilgilerinizi yönetebilirsiniz.</p>
</div>
<div class="stat-grid">
    <div class="stat-card"><span class="ic"><?= icon('package', '', 22) ?></span><strong><?= $stats['orders'] ?></strong><span>Toplam sipariş</span></div>
    <div class="stat-card"><span class="ic"><?= icon('truck', '', 22) ?></span><strong><?= $stats['active'] ?></strong><span>Aktif sipariş</span></div>
    <div class="stat-card"><span class="ic"><?= icon('heart', '', 22) ?></span><strong><?= $stats['wishlist'] ?></strong><span>Favori ürün</span></div>
    <div class="stat-card"><span class="ic"><?= icon('wallet', '', 22) ?></span><strong><?= e(money($stats['spent'])) ?></strong><span>Toplam alışveriş</span></div>
</div>
<div class="card mt-3">
    <div class="card-head"><h3>Son Siparişlerim</h3><a href="<?= e(url('hesabim/siparisler')) ?>" class="view-all">Tümü <?= icon('arrow-right', '', 15) ?></a></div>
    <?php if ($recentOrders): ?>
        <div style="overflow-x:auto">
            <table class="items-table">
                <thead><tr><th>Sipariş No</th><th class="hide-sm">Tarih</th><th>Durum</th><th class="text-right">Tutar</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><strong style="color:var(--ink)">#<?= e($o['order_no']) ?></strong></td>
                        <td class="hide-sm text-muted"><?= e(date_tr($o['created_at'])) ?></td>
                        <td><?= order_status_badge($o['status']) ?></td>
                        <td class="text-right fw-600"><?= e(money($o['total'])) ?></td>
                        <td class="text-right"><a href="<?= e(url('hesabim/siparis/' . $o['order_no'])) ?>" class="btn btn-outline btn-sm">Detay</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-body"><?= partial('partials/empty', ['icon' => 'package', 'title' => 'Henüz siparişiniz yok', 'text' => 'İlk siparişinizi vermek için alışverişe başlayın.', 'action' => ['Alışverişe Başla', 'urunler']]) ?></div>
    <?php endif; ?>
</div>
<?php $inner = ob_get_clean(); echo partial('account/layout', ['inner' => $inner]); ?>

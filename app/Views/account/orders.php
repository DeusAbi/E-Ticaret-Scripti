<?php use App\Services\Order; ob_start(); ?>
<div class="page-title mb-2"><h1>Siparişlerim</h1></div>
<div class="filter-pills">
    <a href="<?= e(url('hesabim/siparisler')) ?>" class="<?= $status === '' ? 'is-active' : '' ?>">Tümü</a>
    <?php foreach (Order::STATUSES as $key => $s): ?>
        <a href="<?= e(url('hesabim/siparisler?durum=' . $key)) ?>" class="<?= $status === $key ? 'is-active' : '' ?>"><?= e($s['label']) ?></a>
    <?php endforeach; ?>
</div>
<?php if ($orders): ?>
    <?php foreach ($orders as $o): $imgs = $thumbs[$o['id']] ?? []; ?>
        <div class="order-card">
            <div class="order-card-head">
                <div><small>Sipariş No</small><strong>#<?= e($o['order_no']) ?></strong></div>
                <div><small>Tarih</small><strong><?= e(date_tr($o['created_at'])) ?></strong></div>
                <div><small>Tutar</small><strong><?= e(money($o['total'])) ?></strong></div>
                <div><small>Ödeme</small><strong><?= e(Order::PAYMENT_METHODS[$o['payment_method']]['label'] ?? '') ?></strong></div>
                <div class="actions"><?= order_status_badge($o['status']) ?></div>
            </div>
            <div class="order-card-body">
                <div class="thumbs">
                    <?php foreach (array_slice($imgs, 0, 4) as $t): ?><img src="<?= e(media($t['image'])) ?>" alt="<?= e($t['product_name']) ?>" title="<?= e($t['product_name']) ?>" loading="lazy"><?php endforeach; ?>
                    <?php if (count($imgs) > 4): ?><span class="more">+<?= count($imgs) - 4 ?></span><?php endif; ?>
                </div>
                <div class="small text-muted" style="flex:1"><?= (int) $o['item_count'] ?> çeşit ürün<?= $o['tracking_no'] ? ' · Kargo takip: <strong>' . e($o['tracking_no']) . '</strong>' : '' ?></div>
                <a href="<?= e(url('hesabim/siparis/' . $o['order_no'])) ?>" class="btn btn-outline btn-sm">Sipariş Detayı <?= icon('chevron-right', '', 16) ?></a>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <?= partial('partials/empty', ['icon' => 'package', 'title' => 'Sipariş bulunamadı', 'text' => $status ? 'Bu durumda bir siparişiniz bulunmuyor.' : 'Henüz sipariş vermediniz.', 'action' => ['Alışverişe Başla', 'urunler']]) ?>
<?php endif; ?>
<?php $inner = ob_get_clean(); echo partial('account/layout', ['inner' => $inner, 'crumb' => 'Siparişlerim']); ?>

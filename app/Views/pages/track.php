<?php use App\Services\Order; ?>
<div class="container" style="max-width:860px">
    <div class="page-head">
        <?= partial('partials/breadcrumbs', ['items' => [['Sipariş Takibi', null]]]) ?>
        <div class="page-title"><div><h1>Sipariş Takibi</h1><p>Sipariş numaranız ve e-posta adresinizle siparişinizin durumunu sorgulayın.</p></div></div>
    </div>
    <form class="card card-body mt-2" method="get" action="<?= e(url('siparis-takip')) ?>">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Sipariş Numarası</label><div class="input-icon"><?= icon('package', '', 18) ?><input class="form-control" name="no" value="<?= e($no) ?>" placeholder="SP2610061234" required></div></div>
            <div class="form-group"><label class="form-label">E-posta Adresi</label><div class="input-icon"><?= icon('mail', '', 18) ?><input class="form-control" type="email" name="email" value="<?= e($email) ?>" placeholder="ornek@mail.com" required></div></div>
        </div>
        <button class="btn btn-dark" type="submit"><?= icon('search', '', 17) ?> Siparişi Sorgula</button>
    </form>

    <?php if ($searched && !$order): ?>
        <div class="alert alert-error mt-3"><?= icon('alert', '', 18) ?> Girdiğiniz bilgilerle eşleşen bir sipariş bulunamadı. Lütfen bilgilerinizi kontrol edin.</div>
    <?php elseif ($order): ?>
        <div class="card mt-3 mb-3">
            <div class="card-head"><h3>#<?= e($order['order_no']) ?></h3><?= order_status_badge($order['status']) ?></div>
            <div class="card-body">
                <?= partial('partials/order-timeline', ['order' => $order, 'history' => $history]) ?>
                <hr>
                <div class="info-list">
                    <div><span>Sipariş Tarihi</span><strong><?= e(date_tr($order['created_at'])) ?></strong></div>
                    <div><span>Toplam Tutar</span><strong><?= e(money($order['total'])) ?></strong></div>
                    <div><span>Ödeme</span><strong><?= e(Order::PAYMENT_METHODS[$order['payment_method']]['label'] ?? '') ?> · <?= e(Order::PAYMENT_STATUSES[$order['payment_status']]['label'] ?? '') ?></strong></div>
                    <div><span>Teslimat</span><strong><?= e($order['ship_district']) ?> / <?= e($order['ship_city']) ?></strong></div>
                    <?php if ($order['tracking_no']): ?><div><span>Kargo</span><strong><?= e($order['cargo_company']) ?> · <?= e($order['tracking_no']) ?></strong></div><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php use App\Services\Order; /** @var array $order @var array $items */ ?>
<div class="container">
    <?= partial('partials/checkout-steps', ['current' => 3]) ?>
    <div class="success-hero">
        <div class="success-icon"><?= icon('check', '', 44, 2.5) ?></div>
        <h1>Siparişiniz alındı, teşekkürler!</h1>
        <p>Sipariş detaylarınızı <strong><?= e($order['customer_email']) ?></strong> adresine gönderdik. Siparişinizin durumunu “Siparişlerim” sayfasından takip edebilirsiniz.</p>
        <div class="order-no-box">Sipariş No: <strong><?= e($order['order_no']) ?></strong> <button class="copy-btn" data-copy="<?= e($order['order_no']) ?>"><?= icon('copy', '', 14) ?></button></div>
    </div>

    <div class="success-grid">
        <div class="card">
            <div class="card-head"><h3><?= icon('package', '', 19) ?> Sipariş Detayı</h3><?= order_status_badge($order['status']) ?></div>
            <div class="card-body">
                <ul class="order-lines">
                    <?php foreach ($items as $item): ?>
                        <li class="order-line">
                            <span class="thumb"><img src="<?= e(media($item['image'])) ?>" alt=""><span class="q"><?= (int) $item['quantity'] ?></span></span>
                            <span class="name"><?= e($item['product_name']) ?></span>
                            <span class="val"><?= e(money($item['total'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="info-list">
                    <div><span>Ara Toplam</span><strong><?= e(money($order['subtotal'])) ?></strong></div>
                    <?php if ((float) $order['discount'] > 0): ?><div><span>İndirim</span><strong class="text-success">-<?= e(money($order['discount'])) ?></strong></div><?php endif; ?>
                    <div><span>Kargo</span><strong><?= (float) $order['shipping_fee'] > 0 ? e(money($order['shipping_fee'])) : 'Ücretsiz' ?></strong></div>
                    <?php if ((float) $order['payment_fee'] > 0): ?><div><span>Kapıda Ödeme Bedeli</span><strong><?= e(money($order['payment_fee'])) ?></strong></div><?php endif; ?>
                </div>
                <div class="summary-total"><span>Toplam</span><strong><?= e(money($order['total'])) ?></strong></div>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><h3><?= icon(Order::PAYMENT_METHODS[$order['payment_method']]['icon'], '', 19) ?> <?= e(Order::PAYMENT_METHODS[$order['payment_method']]['label']) ?></h3><?= payment_status_badge($order['payment_status']) ?></div>
            <div class="card-body">
                <?php if ($order['payment_method'] === 'transfer'): ?>
                    <div class="alert alert-warning"><?= icon('info', '', 18) ?> <span>Siparişinizin hazırlanabilmesi için lütfen <strong><?= e(money($order['total'])) ?></strong> tutarındaki ödemeyi aşağıdaki hesaplardan birine yapın ve açıklamaya <strong><?= e($order['order_no']) ?></strong> yazın.</span></div>
                    <div class="bank-list">
                        <?php foreach ($banks as $b): ?>
                            <div class="bank"><div><strong><?= e($b['bank']) ?></strong><span class="small text-muted"><?= e($b['holder']) ?></span><br><code><?= e($b['iban']) ?></code></div><button type="button" class="copy-btn" data-copy="<?= e(str_replace(' ', '', $b['iban'])) ?>"><?= icon('copy', '', 14) ?> Kopyala</button></div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($order['payment_method'] === 'card'): ?>
                    <div class="alert alert-success"><?= icon('check-circle', '', 18) ?> <span>Ödemeniz başarıyla alındı. Referans: <strong><?= e($order['payment_ref']) ?></strong></span></div>
                <?php else: ?>
                    <div class="alert alert-info"><?= icon('wallet', '', 18) ?> <span>Ödemenizi teslimat sırasında kuryeye nakit veya kartla yapabilirsiniz.</span></div>
                <?php endif; ?>
                <h4 class="mt-2">Teslimat Adresi</h4>
                <p class="mb-0"><strong style="color:var(--ink)"><?= e($order['customer_name']) ?></strong> · <?= e($order['customer_phone']) ?><br><?= e($order['ship_address']) ?><br><?= e($order['ship_district']) ?> / <?= e($order['ship_city']) ?></p>
                <div class="flex gap-1 mt-3 wrap">
                    <a href="<?= e(url('hesabim/siparis/' . $order['order_no'])) ?>" class="btn btn-dark"><?= icon('package', '', 17) ?> Siparişimi Görüntüle</a>
                    <a href="<?= e(url('/')) ?>" class="btn btn-outline">Alışverişe Devam Et</a>
                </div>
            </div>
        </div>
    </div>
</div>

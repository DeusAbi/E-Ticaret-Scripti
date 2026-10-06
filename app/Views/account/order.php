<?php use App\Services\Order; ob_start(); /** @var array $order @var array $items @var array $history */ ?>
<div class="page-title mb-2">
    <div><h1>Sipariş #<?= e($order['order_no']) ?></h1><p><?= e(date_tr($order['created_at'])) ?> tarihinde verildi</p></div>
    <div class="flex gap-1 wrap">
        <?= order_status_badge($order['status']) ?> <?= payment_status_badge($order['payment_status']) ?>
    </div>
</div>

<div class="card card-body mb-2"><?= partial('partials/order-timeline', ['order' => $order, 'history' => $history]) ?></div>

<?php if ($order['tracking_no']): ?>
    <div class="alert alert-info"><?= icon('truck', '', 18) ?> <span><strong><?= e($order['cargo_company']) ?></strong> · Takip numarası: <strong><?= e($order['tracking_no']) ?></strong> <button class="copy-btn" data-copy="<?= e($order['tracking_no']) ?>" style="margin-left:6px"><?= icon('copy', '', 13) ?> Kopyala</button></span></div>
<?php endif; ?>

<?php if ($order['payment_method'] === 'transfer' && $order['payment_status'] === 'pending' && !in_array($order['status'], ['cancelled', 'refunded'], true)): ?>
    <div class="card mb-2">
        <div class="card-head"><h3><?= icon('banknote', '', 19) ?> Ödeme Bekleniyor</h3></div>
        <div class="card-body">
            <p class="small text-muted"><strong><?= e(money($order['total'])) ?></strong> tutarındaki ödemenizi aşağıdaki hesaplardan birine yapın ve açıklamaya <strong><?= e($order['order_no']) ?></strong> yazın.</p>
            <div class="bank-list">
                <?php foreach ($banks as $b): ?>
                    <div class="bank"><div><strong><?= e($b['bank']) ?></strong><span class="small text-muted"><?= e($b['holder']) ?></span><br><code><?= e($b['iban']) ?></code></div><button type="button" class="copy-btn" data-copy="<?= e(str_replace(' ', '', $b['iban'])) ?>"><?= icon('copy', '', 14) ?> Kopyala</button></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card mb-2">
    <div class="card-head"><h3>Ürünler</h3></div>
    <div style="overflow-x:auto">
        <table class="items-table">
            <thead><tr><th>Ürün</th><th class="hide-sm">Birim Fiyat</th><th>Adet</th><th class="text-right">Toplam</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><div class="prod"><img src="<?= e(media($it['image'])) ?>" alt=""><div><strong><?= e($it['product_name']) ?></strong><?php if ($it['sku']): ?><div class="small text-muted"><?= e($it['sku']) ?></div><?php endif; ?></div></div></td>
                    <td class="hide-sm"><?= e(money($it['price'])) ?></td>
                    <td><?= (int) $it['quantity'] ?></td>
                    <td class="text-right fw-600"><?= e(money($it['total'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="address-grid">
    <div class="card card-body">
        <h3 class="mb-2">Teslimat Bilgileri</h3>
        <p class="mb-0"><strong style="color:var(--ink)"><?= e($order['customer_name']) ?></strong><br><?= e($order['customer_phone']) ?><br><?= e($order['ship_address']) ?><br><?= e($order['ship_district']) ?> / <?= e($order['ship_city']) ?> <?= e($order['ship_postal']) ?></p>
        <?php if ($order['invoice_type'] === 'corporate'): ?>
            <hr><p class="small mb-0"><strong>Kurumsal Fatura:</strong> <?= e($order['invoice_company']) ?><br><?= e($order['invoice_tax_office']) ?> V.D. · <?= e($order['invoice_tax_no']) ?></p>
        <?php endif; ?>
        <?php if ($order['note']): ?><hr><p class="small mb-0"><strong>Sipariş notu:</strong> <?= e($order['note']) ?></p><?php endif; ?>
    </div>
    <div class="card card-body">
        <h3 class="mb-2">Ödeme Özeti</h3>
        <div class="info-list">
            <div><span>Ödeme Yöntemi</span><strong><?= e(Order::PAYMENT_METHODS[$order['payment_method']]['label'] ?? '') ?></strong></div>
            <div><span>Ara Toplam</span><strong><?= e(money($order['subtotal'])) ?></strong></div>
            <?php if ((float) $order['discount'] > 0): ?><div><span>İndirim (<?= e($order['coupon_code']) ?>)</span><strong class="text-success">-<?= e(money($order['discount'])) ?></strong></div><?php endif; ?>
            <div><span>Kargo</span><strong><?= (float) $order['shipping_fee'] > 0 ? e(money($order['shipping_fee'])) : 'Ücretsiz' ?></strong></div>
            <?php if ((float) $order['payment_fee'] > 0): ?><div><span>Kapıda Ödeme Bedeli</span><strong><?= e(money($order['payment_fee'])) ?></strong></div><?php endif; ?>
        </div>
        <div class="summary-total"><span>Toplam</span><strong><?= e(money($order['total'])) ?></strong></div>
    </div>
</div>

<div class="flex gap-1 mt-3 wrap">
    <a href="<?= e(url('hesabim/siparisler')) ?>" class="btn btn-outline"><?= icon('arrow-left', '', 17) ?> Siparişlerime Dön</a>
    <button class="btn btn-ghost" onclick="window.print()"><?= icon('printer', '', 17) ?> Yazdır</button>
    <?php if (in_array($order['status'], ['pending', 'processing'], true)): ?>
        <form action="<?= e(url('hesabim/siparis/' . $order['order_no'] . '/iptal')) ?>" method="post" data-confirm="Siparişinizi iptal etmek istediğinize emin misiniz?" style="margin-left:auto">
            <?= csrf_field() ?>
            <button class="btn btn-danger" type="submit"><?= icon('x', '', 17) ?> Siparişi İptal Et</button>
        </form>
    <?php endif; ?>
</div>
<?php $inner = ob_get_clean(); echo partial('account/layout', ['inner' => $inner, 'crumb' => 'Sipariş #' . $order['order_no']]); ?>

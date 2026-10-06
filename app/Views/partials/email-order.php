<?php use App\Services\Order; ?>
<p>Merhaba <?= e($order['customer_name']) ?>,</p>
<p><strong>#<?= e($order['order_no']) ?></strong> numaralı siparişiniz başarıyla alındı. Siparişinizin durumundaki gelişmeleri e-posta ile bildireceğiz.</p>
<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:16px 0">
    <?php foreach ($items as $item): ?>
        <tr style="border-bottom:1px solid #eef0f4">
            <td><?= e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></td>
            <td align="right"><strong><?= e(money($item['total'])) ?></strong></td>
        </tr>
    <?php endforeach; ?>
    <tr><td>Ara Toplam</td><td align="right"><?= e(money($order['subtotal'])) ?></td></tr>
    <?php if ((float) $order['discount'] > 0): ?><tr><td>İndirim (<?= e($order['coupon_code']) ?>)</td><td align="right">-<?= e(money($order['discount'])) ?></td></tr><?php endif; ?>
    <tr><td>Kargo</td><td align="right"><?= (float) $order['shipping_fee'] > 0 ? e(money($order['shipping_fee'])) : 'Ücretsiz' ?></td></tr>
    <?php if ((float) $order['payment_fee'] > 0): ?><tr><td>Kapıda Ödeme Hizmet Bedeli</td><td align="right"><?= e(money($order['payment_fee'])) ?></td></tr><?php endif; ?>
    <tr><td><strong>Genel Toplam</strong></td><td align="right"><strong style="font-size:18px"><?= e(money($order['total'])) ?></strong></td></tr>
</table>
<p><strong>Ödeme yöntemi:</strong> <?= e(Order::PAYMENT_METHODS[$order['payment_method']]['label'] ?? '') ?><br>
<strong>Teslimat adresi:</strong> <?= e($order['ship_address']) ?>, <?= e($order['ship_district']) ?> / <?= e($order['ship_city']) ?></p>
<?php if ($order['payment_method'] === 'transfer'): ?>
    <p>Havale/EFT ile ödemenizi yaparken açıklama kısmına <strong><?= e($order['order_no']) ?></strong> yazmayı unutmayın.</p>
    <?php foreach (bank_accounts() as $b): ?>
        <p style="margin:4px 0"><?= e($b['bank']) ?> — <?= e($b['holder']) ?><br><code><?= e($b['iban']) ?></code></p>
    <?php endforeach; ?>
<?php endif; ?>
<p><a href="<?= e(absolute_url('hesabim/siparis/' . $order['order_no'])) ?>">Siparişinizi görüntüleyin →</a></p>

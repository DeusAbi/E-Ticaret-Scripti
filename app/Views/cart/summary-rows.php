<?php /** @var array $summary */ ?>
<div class="summary-rows">
    <div class="summary-row"><span>Ara Toplam (<?= (int) $summary['count'] ?> ürün)</span><span class="val"><?= e(money($summary['subtotal'])) ?></span></div>
    <?php if ($summary['discount'] > 0): ?>
        <div class="summary-row discount"><span>İndirim <?= $summary['coupon'] ? '(' . e($summary['coupon']['code']) . ')' : '' ?></span><span class="val">-<?= e(money($summary['discount'])) ?></span></div>
    <?php endif; ?>
    <div class="summary-row"><span>Kargo</span><span class="val"><?= $summary['shipping'] > 0 ? e(money($summary['shipping'])) : '<span class="text-success">Ücretsiz</span>' ?></span></div>
    <div class="summary-row<?= $summary['payment_fee'] > 0 ? '' : ' hidden' ?>" data-cod-row><span>Kapıda Ödeme Bedeli</span><span class="val"><?= e(money(setting('cod_fee'))) ?></span></div>
</div>
<div class="summary-total"><span>Toplam</span><strong data-grand-total data-base="<?= e((string) ($summary['total'] - $summary['payment_fee'])) ?>"><?= e(money($summary['total'])) ?></strong></div>
<div class="tax-note">KDV dahil · İçerdiği KDV: <?= e(money($summary['tax_included'])) ?></div>

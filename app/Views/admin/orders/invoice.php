<?php use App\Services\Order; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sipariş #<?= e($order['order_no']) ?> - Fatura</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body style="background:#f4f5f8">
<div class="no-print" style="text-align:center;padding:20px"><button class="btn btn-dark" onclick="window.print()"><?= icon('printer', '', 17) ?> Yazdır</button></div>
<div class="invoice">
    <div class="invoice-head">
        <div>
            <h2><?= e(setting('store_name')) ?><span style="color:var(--primary)">.</span></h2>
            <div class="small text-muted"><?= e(setting('store_company')) ?><br><?= e(setting('store_address')) ?><br><?= e(setting('store_tax_info')) ?> · Mersis: <?= e(setting('store_mersis')) ?></div>
        </div>
        <div class="text-right">
            <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)">Sipariş Özeti</div>
            <div style="font-size:22px;font-weight:800;color:var(--ink)">#<?= e($order['order_no']) ?></div>
            <div class="small"><?= e(date('d.m.Y H:i', strtotime($order['created_at']))) ?></div>
        </div>
    </div>
    <div class="invoice-cols">
        <div>
            <h4>Alıcı / Teslimat</h4>
            <strong><?= e($order['customer_name']) ?></strong><br><?= e($order['ship_address']) ?><br><?= e($order['ship_district']) ?> / <?= e($order['ship_city']) ?><br><?= e($order['customer_phone']) ?> · <?= e($order['customer_email']) ?>
        </div>
        <div>
            <h4>Fatura Bilgisi</h4>
            <?php if ($order['invoice_type'] === 'corporate'): ?>
                <strong><?= e($order['invoice_company']) ?></strong><br><?= e($order['invoice_tax_office']) ?> V.D. · <?= e($order['invoice_tax_no']) ?>
            <?php else: ?>Bireysel<?php endif; ?>
            <br>Ödeme: <?= e(Order::PAYMENT_METHODS[$order['payment_method']]['label'] ?? '') ?>
        </div>
    </div>
    <table>
        <thead><tr><th>Ürün</th><th>SKU</th><th class="r">Birim</th><th class="r">Adet</th><th class="r">Tutar</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr><td><?= e($it['product_name']) ?></td><td><?= e($it['sku']) ?></td><td class="r"><?= e(money($it['price'])) ?></td><td class="r"><?= (int) $it['quantity'] ?></td><td class="r"><?= e(money($it['total'])) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="totals">
        <div><span>Ara Toplam</span><span><?= e(money($order['subtotal'])) ?></span></div>
        <?php if ((float) $order['discount'] > 0): ?><div><span>İndirim</span><span>-<?= e(money($order['discount'])) ?></span></div><?php endif; ?>
        <div><span>Kargo</span><span><?= e(money($order['shipping_fee'])) ?></span></div>
        <?php if ((float) $order['payment_fee'] > 0): ?><div><span>Kapıda Ödeme</span><span><?= e(money($order['payment_fee'])) ?></span></div><?php endif; ?>
        <div><span>Dahil KDV</span><span><?= e(money($order['tax_total'])) ?></span></div>
        <div class="grand"><span>Genel Toplam</span><span><?= e(money($order['total'])) ?></span></div>
    </div>
    <p class="small text-muted mt-4">Bu belge bilgilendirme amaçlıdır; yasal fatura yerine geçmez.</p>
</div>
</body>
</html>

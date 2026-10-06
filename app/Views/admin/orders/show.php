<?php use App\Services\Order; /** @var array $order @var array $items @var array $history @var ?array $customer @var array $cargos */ ?>
<?= partial('admin/partials-head', [
    'title' => 'Sipariş #' . $order['order_no'],
    'sub' => date_tr($order['created_at']) . ' · ' . time_ago($order['created_at']),
    'crumbs' => [['Siparişler', 'admin/siparisler'], ['#' . $order['order_no'], null]],
    'actions' => order_status_badge($order['status']) . ' ' . payment_status_badge($order['payment_status'])
        . ' <a href="' . e(url('admin/siparisler/' . $order['id'] . '/yazdir')) . '" target="_blank" class="btn btn-outline btn-sm">' . icon('printer', '', 16) . ' Fatura / İrsaliye</a>',
]) ?>

<div class="order-grid">
    <div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('package', '', 18) ?> Ürünler</h3><span class="small text-muted"><?= array_sum(array_column($items, 'quantity')) ?> adet</span></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Ürün</th><th>Birim Fiyat</th><th>Adet</th><th class="text-right">Toplam</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><div class="prod"><img src="<?= e(media($it['image'])) ?>" alt=""><div><?php if ($it['product_id']): ?><a href="<?= e(url('admin/urunler/' . $it['product_id'] . '/duzenle')) ?>"><?= e($it['product_name']) ?></a><?php else: ?><strong><?= e($it['product_name']) ?></strong><?php endif; ?><small><?= e($it['sku'] ?: '') ?></small></div></div></td>
                            <td class="num"><?= e(money($it['price'])) ?></td>
                            <td>× <?= (int) $it['quantity'] ?></td>
                            <td class="text-right num fw-600"><?= e(money($it['total'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="panel-body" style="border-top:1px solid var(--line-2)">
                <div class="totals">
                    <div><span class="text-muted">Ara Toplam</span><span><?= e(money($order['subtotal'])) ?></span></div>
                    <?php if ((float) $order['discount'] > 0): ?><div><span class="text-muted">İndirim (<?= e($order['coupon_code']) ?>)</span><span class="text-success">-<?= e(money($order['discount'])) ?></span></div><?php endif; ?>
                    <div><span class="text-muted">Kargo</span><span><?= (float) $order['shipping_fee'] > 0 ? e(money($order['shipping_fee'])) : 'Ücretsiz' ?></span></div>
                    <?php if ((float) $order['payment_fee'] > 0): ?><div><span class="text-muted">Kapıda Ödeme Bedeli</span><span><?= e(money($order['payment_fee'])) ?></span></div><?php endif; ?>
                    <div><span class="text-muted">Dahil KDV</span><span><?= e(money($order['tax_total'])) ?></span></div>
                    <div class="grand"><span>Genel Toplam</span><span><?= e(money($order['total'])) ?></span></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3><?= icon('truck', '', 18) ?> Durum & Kargo</h3></div>
            <form class="panel-body" action="<?= e(url('admin/siparisler/' . $order['id'] . '/durum')) ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Sipariş Durumu</label>
                        <select class="form-control" name="status" data-status-select>
                            <?php foreach (Order::STATUSES as $k => $s): ?><option value="<?= e($k) ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= e($s['label']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kargo Firması</label>
                        <input class="form-control" name="cargo_company" list="cargos" value="<?= e(old('cargo_company', $order['cargo_company'])) ?>" placeholder="Seçin veya yazın">
                        <datalist id="cargos"><?php foreach ($cargos as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Takip Numarası</label>
                        <input class="form-control<?= has_error('tracking_no') ?>" name="tracking_no" value="<?= e(old('tracking_no', $order['tracking_no'])) ?>">
                    </div>
                </div>
                <?= field_error('tracking_no') ?>
                <div class="form-group">
                    <label class="form-label">Not (sipariş geçmişine eklenir)</label>
                    <input class="form-control" name="note" maxlength="500" placeholder="Örn. Ürün depodan çıktı">
                </div>
                <div class="flex items-center justify-between wrap gap-2">
                    <label class="check"><input type="checkbox" name="notify" value="1" checked> Müşteriye e-posta ile bildir</label>
                    <button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> Güncelle</button>
                </div>
                <?php if (in_array($order['status'], ['cancelled', 'refunded'], true)): ?>
                    <p class="form-hint mt-1">Not: İptal/iade edilen siparişin stokları iade edilmiştir. Siparişi tekrar aktif bir duruma alırsanız stoklar yeniden düşülür.</p>
                <?php else: ?>
                    <p class="form-hint mt-1">Sipariş "İptal" veya "İade" durumuna alındığında ürün stokları otomatik olarak geri eklenir.</p>
                <?php endif; ?>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head"><h3><?= icon('clock', '', 18) ?> Sipariş Geçmişi</h3></div>
            <div class="panel-body">
                <ul class="history">
                    <?php foreach ($history as $h): ?>
                        <li><strong style="color:var(--ink)"><?= e(Order::statusLabel($h['status'])) ?></strong><?= $h['note'] ? ' — ' . e($h['note']) : '' ?><small><?= e(date_tr($h['created_at'])) ?><?= $h['user_name'] ? ' · ' . e($h['user_name']) : '' ?></small></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="form-side" style="top:88px">
        <div class="panel">
            <div class="panel-head"><h3><?= icon('user', '', 18) ?> Müşteri</h3></div>
            <div class="panel-body">
                <div class="customer-box">
                    <span class="avatar"><?= e(initials($order['customer_name'])) ?></span>
                    <div><strong><?= e($order['customer_name']) ?></strong><span><?= $customer ? (int) $customer['order_count'] . ' sipariş · Üye' : 'Misafir' ?></span></div>
                </div>
                <div class="kv">
                    <div><span>E-posta</span><strong><a href="mailto:<?= e($order['customer_email']) ?>"><?= e($order['customer_email']) ?></a></strong></div>
                    <div><span>Telefon</span><strong><a href="tel:<?= e($order['customer_phone']) ?>"><?= e($order['customer_phone']) ?></a></strong></div>
                </div>
                <?php if ($customer): ?><a class="btn btn-outline btn-sm btn-block mt-2" href="<?= e(url('admin/musteriler/' . $customer['id'])) ?>">Müşteri Profili</a><?php endif; ?>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('pin', '', 18) ?> Teslimat Adresi</h3><button class="btn btn-ghost btn-sm" data-copy="<?= e($order['customer_name'] . "\n" . $order['ship_address'] . "\n" . $order['ship_district'] . ' / ' . $order['ship_city'] . "\n" . $order['customer_phone']) ?>"><?= icon('copy', '', 14) ?></button></div>
            <div class="panel-body">
                <p class="mb-0"><?= e($order['ship_address']) ?><br><?= e($order['ship_district']) ?> / <?= e($order['ship_city']) ?> <?= e($order['ship_postal']) ?></p>
                <?php if ($order['invoice_type'] === 'corporate'): ?>
                    <hr><div class="kv"><div><span>Firma</span><strong><?= e($order['invoice_company']) ?></strong></div><div><span>Vergi D.</span><strong><?= e($order['invoice_tax_office']) ?></strong></div><div><span>Vergi No</span><strong><?= e($order['invoice_tax_no']) ?></strong></div></div>
                <?php endif; ?>
                <?php if ($order['note']): ?><div class="note-box mt-2"><strong>Müşteri notu:</strong> <?= e($order['note']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('card', '', 18) ?> Ödeme</h3></div>
            <div class="panel-body">
                <div class="kv mb-2">
                    <div><span>Yöntem</span><strong><?= e(Order::PAYMENT_METHODS[$order['payment_method']]['label'] ?? '') ?></strong></div>
                    <div><span>Durum</span><strong><?= payment_status_badge($order['payment_status']) ?></strong></div>
                    <?php if ($order['payment_ref']): ?><div><span>Referans</span><strong><?= e($order['payment_ref']) ?></strong></div><?php endif; ?>
                    <?php if ($order['ip_address']): ?><div><span>IP</span><strong><?= e($order['ip_address']) ?></strong></div><?php endif; ?>
                </div>
                <form class="flex gap-1" action="<?= e(url('admin/siparisler/' . $order['id'] . '/odeme')) ?>" method="post">
                    <?= csrf_field() ?>
                    <select class="form-control" name="payment_status" style="height:40px">
                        <?php foreach (Order::PAYMENT_STATUSES as $k => $s): ?><option value="<?= e($k) ?>" <?= $order['payment_status'] === $k ? 'selected' : '' ?>><?= e($s['label']) ?></option><?php endforeach; ?>
                    </select>
                    <button class="btn btn-dark btn-sm" style="height:40px" type="submit">Kaydet</button>
                </form>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('edit', '', 18) ?> Dahili Not</h3></div>
            <form class="panel-body" action="<?= e(url('admin/siparisler/' . $order['id'] . '/not')) ?>" method="post">
                <?= csrf_field() ?>
                <textarea class="form-control" name="admin_note" rows="3" placeholder="Sadece yöneticiler görür…"><?= e($order['admin_note']) ?></textarea>
                <button class="btn btn-outline btn-sm mt-1" type="submit">Notu Kaydet</button>
            </form>
        </div>
    </div>
</div>

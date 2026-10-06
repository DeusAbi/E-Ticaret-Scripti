<?php use App\Services\Order; $status = (string) input('durum', ''); ?>
<?= partial('admin/partials-head', [
    'title' => 'Siparişler',
    'sub' => 'Siparişleri görüntüleyin, durumlarını güncelleyin ve kargo bilgisi girin.',
    'actions' => '<a href="' . e(query_url(['export' => 'csv', 'sayfa' => null])) . '" class="btn btn-outline">' . icon('file', '', 17) . ' Excel (CSV) İndir</a>',
]) ?>
<div class="seg">
    <a href="<?= e(url('admin/siparisler')) ?>" class="<?= $status === '' ? 'is-active' : '' ?>">Tümü <span class="n"><?= $counts[''] ?></span></a>
    <?php foreach (Order::STATUSES as $k => $s): ?>
        <a href="<?= e(url('admin/siparisler?durum=' . $k)) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= e($s['label']) ?> <span class="n"><?= $counts[$k] ?? 0 ?></span></a>
    <?php endforeach; ?>
</div>
<div class="panel">
    <form class="filterbar" method="get">
        <?php if ($status): ?><input type="hidden" name="durum" value="<?= e($status) ?>"><?php endif; ?>
        <input class="form-control grow" type="search" name="q" value="<?= e(input('q', '')) ?>" placeholder="Sipariş no, müşteri, e-posta veya telefon…">
        <select class="form-control" name="odeme">
            <option value="">Tüm ödeme yöntemleri</option>
            <?php foreach (Order::PAYMENT_METHODS as $k => $m): ?><option value="<?= e($k) ?>" <?= input('odeme') === $k ? 'selected' : '' ?>><?= e($m['label']) ?></option><?php endforeach; ?>
        </select>
        <input class="form-control" type="date" name="baslangic" value="<?= e(input('baslangic', '')) ?>" title="Başlangıç tarihi">
        <input class="form-control" type="date" name="bitis" value="<?= e(input('bitis', '')) ?>" title="Bitiş tarihi">
        <button class="btn btn-dark" type="submit"><?= icon('filter', '', 16) ?> Filtrele</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Sipariş</th><th>Tarih</th><th>Müşteri</th><th>Ürün</th><th>Ödeme</th><th>Durum</th><th class="text-right">Tutar</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($result['items'] as $o): ?>
                <tr>
                    <td><a class="link-strong" href="<?= e(url('admin/siparisler/' . $o['id'])) ?>">#<?= e($o['order_no']) ?></a></td>
                    <td class="num"><?= e(date('d.m.Y H:i', strtotime($o['created_at']))) ?><div class="small text-muted"><?= e(time_ago($o['created_at'])) ?></div></td>
                    <td><strong style="color:var(--ink);font-weight:600"><?= e($o['customer_name']) ?></strong><div class="small text-muted"><?= e($o['ship_city']) ?> · <?= e($o['customer_phone']) ?></div></td>
                    <td><?= (int) $o['item_count'] ?> adet</td>
                    <td><span class="small"><?= e(Order::PAYMENT_METHODS[$o['payment_method']]['label'] ?? '') ?></span><div><?= payment_status_badge($o['payment_status']) ?></div></td>
                    <td><?= order_status_badge($o['status']) ?></td>
                    <td class="text-right num"><strong style="color:var(--ink)"><?= e(money($o['total'])) ?></strong></td>
                    <td><div class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('admin/siparisler/' . $o['id'])) ?>">Detay</a></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$result['items']): ?><tr class="empty-row"><td colspan="8">Kriterlere uygun sipariş bulunamadı.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/partials-pagination', ['result' => $result]) ?>
</div>

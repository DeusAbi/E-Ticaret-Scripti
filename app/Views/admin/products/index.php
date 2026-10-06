<?php
use App\Services\Product;
/** @var array $result @var array $categories @var array $counts */
$stockF = (string) input('stok', '');
$statusF = (string) input('durum', '');
?>
<?= partial('admin/partials-head', [
    'title' => 'Ürünler',
    'sub' => 'Ürün kataloğunuzu, stok ve fiyatlarınızı yönetin.',
    'actions' => '<a href="' . e(url('admin/urunler/yeni')) . '" class="btn btn-primary">' . icon('plus', '', 17) . ' Yeni Ürün Ekle</a>',
]) ?>

<div class="seg">
    <a href="<?= e(url('admin/urunler')) ?>" class="<?= !$statusF && !$stockF ? 'is-active' : '' ?>">Tümü <span class="n"><?= $counts['all'] ?></span></a>
    <a href="<?= e(url('admin/urunler?durum=active')) ?>" class="<?= $statusF === 'active' ? 'is-active' : '' ?>">Yayında <span class="n"><?= $counts['active'] ?></span></a>
    <a href="<?= e(url('admin/urunler?durum=draft')) ?>" class="<?= $statusF === 'draft' ? 'is-active' : '' ?>">Taslak / Pasif <span class="n"><?= $counts['draft'] ?></span></a>
    <a href="<?= e(url('admin/urunler?stok=low')) ?>" class="<?= $stockF === 'low' ? 'is-active' : '' ?>">Kritik Stok</a>
    <a href="<?= e(url('admin/urunler?stok=out')) ?>" class="<?= $stockF === 'out' ? 'is-active' : '' ?>">Tükenen <span class="n"><?= $counts['out'] ?></span></a>
</div>

<div class="panel">
    <form class="filterbar" method="get">
        <input class="form-control grow" type="search" name="q" value="<?= e(input('q', '')) ?>" placeholder="Ürün adı, SKU, marka veya barkod ara…">
        <select class="form-control" name="kategori">
            <option value="">Tüm kategoriler</option>
            <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) input('kategori') === (int) $c['id'] ? 'selected' : '' ?>><?= $c['parent_id'] ? '— ' : '' ?><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <select class="form-control" name="durum">
            <option value="">Tüm durumlar</option>
            <?php foreach (Product::STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $statusF === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
        <select class="form-control" name="sirala">
            <option value="">En yeni</option>
            <option value="name" <?= input('sirala') === 'name' ? 'selected' : '' ?>>İsme göre</option>
            <option value="price" <?= input('sirala') === 'price' ? 'selected' : '' ?>>Fiyat (yüksek)</option>
            <option value="stock" <?= input('sirala') === 'stock' ? 'selected' : '' ?>>Stok (az)</option>
            <option value="sales" <?= input('sirala') === 'sales' ? 'selected' : '' ?>>Çok satan</option>
        </select>
        <?php if ($stockF): ?><input type="hidden" name="stok" value="<?= e($stockF) ?>"><?php endif; ?>
        <button class="btn btn-dark" type="submit"><?= icon('filter', '', 16) ?> Filtrele</button>
    </form>

    <form action="<?= e(url('admin/urunler/toplu')) ?>" method="post" data-bulk>
        <?= csrf_field() ?>
        <div class="bulkbar" data-bulkbar>
            <strong data-bulk-count>0</strong> ürün seçildi
            <select class="form-control" name="action">
                <option value="active">Yayına al</option>
                <option value="draft">Taslağa al</option>
                <option value="passive">Pasife al</option>
                <option value="feature">Öne çıkar</option>
                <option value="unfeature">Öne çıkarmayı kaldır</option>
            </select>
            <button class="btn btn-dark btn-sm" type="submit">Uygula</button>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th class="check-col"><input type="checkbox" data-check-all aria-label="Tümünü seç"></th>
                    <th>Ürün</th><th>Kategori</th><th>Fiyat</th><th>Stok</th><th>Satış</th><th>Durum</th><th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($result['items'] as $p): $st = Product::stockState($p); ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" data-check-item aria-label="Seç"></td>
                        <td>
                            <div class="prod">
                                <img src="<?= e(media($p['image'])) ?>" alt="">
                                <div>
                                    <a href="<?= e(url('admin/urunler/' . $p['id'] . '/duzenle')) ?>"><?= e($p['name']) ?></a>
                                    <small><?= e($p['sku'] ?: '—') ?><?= $p['brand'] ? ' · ' . e($p['brand']) : '' ?><?= (int) $p['is_featured'] ? ' · ⭐ Öne çıkan' : '' ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($p['category_name'] ?? '—') ?></td>
                        <td class="num">
                            <strong style="color:var(--ink)"><?= e(money($p['price'])) ?></strong>
                            <?php if ($p['compare_price'] && (float) $p['compare_price'] > (float) $p['price']): ?><div class="small text-muted" style="text-decoration:line-through"><?= e(money($p['compare_price'])) ?></div><?php endif; ?>
                        </td>
                        <td><span class="stock-num <?= $st['key'] === 'out' ? 'out' : ($st['key'] === 'low' ? 'low' : '') ?>"><?= (int) $p['stock'] ?></span> <span class="small text-muted">adet</span></td>
                        <td class="num"><?= (int) $p['sales_count'] ?></td>
                        <td><?= badge(Product::STATUSES[$p['status']] ?? $p['status'], ['active' => 'success', 'draft' => 'muted', 'passive' => 'warning'][$p['status']] ?? 'muted') ?></td>
                        <td>
                            <div class="actions">
                                <?php if ($p['status'] === 'active'): ?><a class="btn btn-ghost btn-icon btn-sm" href="<?= e(url('urun/' . $p['slug'])) ?>" target="_blank" title="Mağazada gör"><?= icon('eye', '', 17) ?></a><?php endif; ?>
                                <a class="btn btn-outline btn-icon btn-sm" href="<?= e(url('admin/urunler/' . $p['id'] . '/duzenle')) ?>" title="Düzenle"><?= icon('edit', '', 16) ?></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$result['items']): ?><tr class="empty-row"><td colspan="8">Filtrelere uygun ürün bulunamadı.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?= partial('admin/partials-pagination', ['result' => $result]) ?>
</div>

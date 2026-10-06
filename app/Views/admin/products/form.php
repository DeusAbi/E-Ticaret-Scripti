<?php
use App\Services\Product;
/** @var ?array $product @var array $images @var array $categories */
$p = $product ?? [];
$v = static fn (string $k, $d = '') => old($k, $p[$k] ?? $d);
$isEdit = (bool) $product;
$action = $isEdit ? url('admin/urunler/' . $p['id']) : url('admin/urunler');
?>
<?= partial('admin/partials-head', [
    'title' => $isEdit ? $p['name'] : 'Yeni Ürün',
    'crumbs' => [['Ürünler', 'admin/urunler'], [$isEdit ? 'Düzenle' : 'Yeni Ürün', null]],
    'actions' => $isEdit ? '<a href="' . e(url('urun/' . $p['slug'])) . '" target="_blank" class="btn btn-outline btn-sm">' . icon('eye', '', 16) . ' Mağazada Gör</a>' : '',
]) ?>

<form action="<?= e($action) ?>" method="post" enctype="multipart/form-data" class="form-layout" id="product-form">
    <?= csrf_field() ?>
    <div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('file', '', 18) ?> Temel Bilgiler</h3></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Ürün Adı <span class="req">*</span></label>
                    <input class="form-control<?= has_error('name') ?>" name="name" value="<?= e($v('name')) ?>" required maxlength="190" placeholder="Örn. Kablosuz Kulak Üstü Kulaklık" data-slug-source>
                    <?= field_error('name') ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Kısa Açıklama</label>
                    <textarea class="form-control<?= has_error('short_description') ?>" name="short_description" rows="2" maxlength="500" placeholder="Ürün listesinde ve detay sayfasının üstünde görünür."><?= e($v('short_description')) ?></textarea>
                    <?= field_error('short_description') ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Detaylı Açıklama</label>
                    <textarea class="form-control" name="description" rows="9" placeholder="Ürün açıklaması…"><?= e($v('description')) ?></textarea>
                    <div class="form-hint">Biçimlendirme: <code>## Başlık</code>, <code>- madde</code>, <code>**kalın**</code>. Paragraflar için boş satır bırakın.</div>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Teknik Özellikler</label>
                    <textarea class="form-control" name="specs" rows="5" placeholder="Renk: Siyah&#10;Ağırlık: 250 g&#10;Garanti: 2 Yıl" style="font-family:ui-monospace,Menlo,monospace;font-size:13.5px"><?= e($v('specs')) ?></textarea>
                    <div class="form-hint">Her satıra bir özellik yazın: <code>Özellik: Değer</code></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3><?= icon('image', '', 18) ?> Görseller</h3><span class="small text-muted">İlk görsel kapak olarak kullanılır</span></div>
            <div class="panel-body">
                <label class="dropzone" data-dropzone>
                    <?= icon('upload', '', 28) ?>
                    <strong>Görsel yüklemek için tıklayın veya sürükleyin</strong>
                    <span>JPG, PNG, WEBP veya GIF · En fazla 5 MB · Birden fazla seçebilirsiniz</span>
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-image-input>
                </label>
                <div class="image-grid" data-image-preview>
                    <?php foreach ($images as $i => $img): ?>
                        <div class="image-item<?= $i === 0 ? ' is-cover' : '' ?>">
                            <img src="<?= e(media($img['path'])) ?>" alt="">
                            <?php if ($i === 0): ?><span class="cover-tag">Kapak</span><?php endif; ?>
                            <div class="img-actions">
                                <?php if ($i > 0): ?><button type="submit" form="cover-<?= (int) $img['id'] ?>" title="Kapak yap"><?= icon('star', '', 15) ?></button><?php endif; ?>
                                <button type="submit" form="del-img-<?= (int) $img['id'] ?>" class="del" title="Sil"><?= icon('trash', '', 15) ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3><?= icon('tag', '', 18) ?> Fiyatlandırma</h3></div>
            <div class="panel-body">
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Satış Fiyatı <span class="req">*</span></label>
                        <div class="input-addon"><input class="form-control<?= has_error('price') ?>" name="price" inputmode="decimal" value="<?= e($v('price') !== '' ? number_format((float) $v('price'), 2, ',', '') : '') ?>" required data-price><span>₺</span></div>
                        <?= field_error('price') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Eski Fiyat (üstü çizili)</label>
                        <div class="input-addon"><input class="form-control<?= has_error('compare_price') ?>" name="compare_price" inputmode="decimal" value="<?= e($v('compare_price') !== '' && $v('compare_price') !== null ? number_format((float) $v('compare_price'), 2, ',', '') : '') ?>" data-compare><span>₺</span></div>
                        <div class="form-hint" data-discount-hint></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Maliyet</label>
                        <div class="input-addon"><input class="form-control" name="cost_price" inputmode="decimal" value="<?= e($v('cost_price') !== '' && $v('cost_price') !== null ? number_format((float) $v('cost_price'), 2, ',', '') : '') ?>" data-cost><span>₺</span></div>
                        <div class="margin-hint" data-margin-hint>Müşteriler bu alanı görmez.</div>
                    </div>
                </div>
                <div class="form-group mb-0" style="max-width:240px">
                    <label class="form-label">KDV Oranı</label>
                    <select class="form-control" name="tax_rate">
                        <?php foreach ([20, 10, 1, 0] as $r): ?><option value="<?= $r ?>" <?= (int) $v('tax_rate', 20) === $r ? 'selected' : '' ?>>%<?= $r ?></option><?php endforeach; ?>
                    </select>
                    <div class="form-hint">Fiyatlar KDV dahil girilir.</div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3><?= icon('package', '', 18) ?> Stok & Envanter</h3></div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stok Kodu (SKU)</label>
                        <input class="form-control<?= has_error('sku') ?>" name="sku" value="<?= e($v('sku')) ?>" maxlength="100" placeholder="Örn. SON-01007">
                        <?= field_error('sku') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Barkod (GTIN/EAN)</label>
                        <input class="form-control" name="barcode" value="<?= e($v('barcode')) ?>" maxlength="100">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stok Adedi <span class="req">*</span></label>
                        <input class="form-control<?= has_error('stock') ?>" type="number" min="0" name="stock" value="<?= e($v('stock', 0)) ?>" required>
                        <?= field_error('stock') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kritik Stok Uyarısı</label>
                        <input class="form-control" type="number" min="0" name="low_stock_threshold" value="<?= e($v('low_stock_threshold', 5)) ?>">
                        <div class="form-hint">Stok bu değerin altına düştüğünde uyarı gösterilir.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3><?= icon('search', '', 18) ?> Arama Motoru (SEO)</h3></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">URL</label>
                    <div class="input-addon left"><span><?= e(rtrim(absolute_url('urun'), '/')) ?>/</span><input class="form-control" name="slug" value="<?= e($v('slug')) ?>" placeholder="otomatik-olusturulur" data-slug></div>
                </div>
                <div class="form-group">
                    <label class="form-label">SEO Başlığı</label>
                    <input class="form-control" name="meta_title" value="<?= e($v('meta_title')) ?>" maxlength="190" placeholder="Boş bırakılırsa ürün adı kullanılır">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">SEO Açıklaması</label>
                    <textarea class="form-control" name="meta_description" rows="2" maxlength="300"><?= e($v('meta_description')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="panel">
            <div class="panel-head"><h3>Yayın Durumu</h3></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-control" name="status">
                        <?php foreach (Product::STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $v('status', 'active') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                    </select>
                    <div class="form-hint">Yalnızca "Yayında" olan ürünler mağazada görünür.</div>
                </div>
                <label class="switch"><input type="checkbox" name="is_featured" value="1" <?= (int) $v('is_featured', 0) ? 'checked' : '' ?>><span class="track"></span><span>Öne çıkan ürün<small>Ana sayfada gösterilir</small></span></label>
            </div>
            <div class="panel-foot">
                <?php if (!$isEdit): ?>
                    <button class="btn btn-outline btn-sm" type="submit" name="after" value="new">Kaydet & Yeni</button>
                <?php else: ?>
                    <span class="small text-muted">Son güncelleme: <?= e(time_ago($p['updated_at'] ?? $p['created_at'])) ?></span>
                <?php endif; ?>
                <button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> Kaydet</button>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h3>Organizasyon</h3></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select class="form-control" name="category_id">
                        <option value="">Kategori seçin</option>
                        <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $v('category_id') === (int) $c['id'] ? 'selected' : '' ?>><?= $c['parent_id'] ? '— ' : '' ?><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                    <div class="form-hint"><a class="link" href="<?= e(url('admin/kategoriler/yeni')) ?>">+ Yeni kategori</a></div>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Marka</label>
                    <input class="form-control" name="brand" value="<?= e($v('brand')) ?>" maxlength="120" list="brand-list">
                    <datalist id="brand-list"><?php foreach (Product::brands() as $b): ?><option value="<?= e($b['brand']) ?>"><?php endforeach; ?></datalist>
                </div>
            </div>
        </div>

        <?php if ($isEdit): ?>
            <div class="panel">
                <div class="panel-head"><h3>İstatistikler</h3></div>
                <div class="panel-body kv">
                    <div><span>Görüntülenme</span><strong><?= (int) $p['views'] ?></strong></div>
                    <div><span>Satış adedi</span><strong><?= (int) $p['sales_count'] ?></strong></div>
                    <div><span>Oluşturulma</span><strong><?= e(date_tr($p['created_at'], false)) ?></strong></div>
                </div>
                <div class="panel-foot">
                    <button class="btn btn-ghost btn-sm" type="submit" form="dup-form"><?= icon('copy', '', 15) ?> Kopyala</button>
                    <button class="btn btn-danger btn-sm" type="submit" form="del-form"><?= icon('trash', '', 15) ?> Sil</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</form>

<?php if ($isEdit): ?>
    <form id="del-form" action="<?= e(url('admin/urunler/' . $p['id'] . '/sil')) ?>" method="post" data-confirm="Bu ürünü silmek istediğinize emin misiniz? Bu işlem geri alınamaz."><?= csrf_field() ?></form>
    <form id="dup-form" action="<?= e(url('admin/urunler/' . $p['id'] . '/kopyala')) ?>" method="post"><?= csrf_field() ?></form>
    <?php foreach ($images as $img): ?>
        <form id="del-img-<?= (int) $img['id'] ?>" action="<?= e(url('admin/urun-gorsel/' . $img['id'] . '/sil')) ?>" method="post" data-confirm="Bu görseli silmek istiyor musunuz?"><?= csrf_field() ?></form>
        <form id="cover-<?= (int) $img['id'] ?>" action="<?= e(url('admin/urun-gorsel/' . $img['id'] . '/kapak')) ?>" method="post"><?= csrf_field() ?></form>
    <?php endforeach; ?>
<?php endif; ?>

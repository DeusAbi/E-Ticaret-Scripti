<?php
/** @var array $result @var array $filters @var array $categories @var array $brands @var ?array $category */
$crumbs = [];
if ($category) {
    foreach ($breadcrumbs as $i => $b) {
        $crumbs[] = [$b['name'], $i === count($breadcrumbs) - 1 ? null : 'kategori/' . $b['slug']];
    }
} else {
    $crumbs[] = [$title, null];
}
$sorts = ['' => 'Önerilen', 'bestseller' => 'En Çok Satanlar', 'newest' => 'En Yeniler', 'price_asc' => 'Fiyat: Düşükten Yükseğe', 'price_desc' => 'Fiyat: Yüksekten Düşüğe', 'rating' => 'En Yüksek Puan'];
$activeCatId = $category ? (int) $category['id'] : 0;
$hasFilters = $filters['brands'] || $filters['min'] !== '' || $filters['max'] !== '' || $filters['in_stock'] || (input('indirim') === '1');
?>
<div class="container">
    <div class="page-head">
        <?= partial('partials/breadcrumbs', ['items' => $crumbs]) ?>
        <div class="page-title">
            <div>
                <h1><?= e($title) ?></h1>
                <?php if ($description): ?><p><?= e($description) ?></p><?php endif; ?>
            </div>
        </div>
        <?php if ($subcategories): ?>
            <div class="subcats">
                <?php foreach ($subcategories as $sc): ?>
                    <a class="subcat" href="<?= e(url('kategori/' . $sc['slug'])) ?>"><span class="sub-icon"><?= icon($sc['icon'] ?: 'tag', '', 15) ?></span><?= e($sc['name']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="listing">
        <aside class="filters" data-filters>
            <div class="filters-close"><strong>Filtrele</strong><button class="btn btn-ghost btn-icon btn-sm" data-filters-close aria-label="Kapat"><?= icon('x', '', 20) ?></button></div>
            <div class="filter-block">
                <h4>Kategoriler</h4>
                <ul class="filter-list">
                    <li><a href="<?= e(url('urunler')) ?>" class="<?= !$category && current_path() === '/urunler' ? 'is-active' : '' ?>">Tüm Ürünler</a></li>
                    <?php foreach ($categories as $c): $sum = (int) $c['product_count'] + array_sum(array_map(static fn ($ch) => (int) $ch['product_count'], $c['children'])); ?>
                        <li><a href="<?= e(url('kategori/' . $c['slug'])) ?>" class="<?= $activeCatId === (int) $c['id'] ? 'is-active' : '' ?>"><?= e($c['name']) ?> <small><?= $sum ?></small></a></li>
                        <?php foreach ($c['children'] as $ch): ?>
                            <li class="child"><a href="<?= e(url('kategori/' . $ch['slug'])) ?>" class="<?= $activeCatId === (int) $ch['id'] ? 'is-active' : '' ?>"><?= e($ch['name']) ?> <small><?= (int) $ch['product_count'] ?></small></a></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="get" action="<?= e(url(ltrim(current_path(), '/'))) ?>">
                <?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= e($filters['q']) ?>"><?php endif; ?>
                <?php if (input('sirala')): ?><input type="hidden" name="sirala" value="<?= e(input('sirala')) ?>"><?php endif; ?>
                <div class="filter-block">
                    <h4>Fiyat Aralığı</h4>
                    <div class="price-range">
                        <input class="form-control" type="number" name="min" min="0" placeholder="En az ₺" value="<?= e($filters['min']) ?>">
                        <input class="form-control" type="number" name="max" min="0" placeholder="En çok ₺" value="<?= e($filters['max']) ?>">
                    </div>
                </div>
                <?php if ($brands): ?>
                    <div class="filter-block">
                        <h4>Marka</h4>
                        <div class="filter-checks">
                            <?php foreach ($brands as $b): ?>
                                <label class="check"><input type="checkbox" name="marka[]" value="<?= e($b['brand']) ?>" <?= in_array($b['brand'], $filters['brands'], true) ? 'checked' : '' ?>> <span><?= e($b['brand']) ?> <small class="text-muted">(<?= (int) $b['cnt'] ?>)</small></span></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="filter-block">
                    <h4>Diğer</h4>
                    <div class="filter-checks">
                        <label class="check"><input type="checkbox" name="stok" value="1" <?= $filters['in_stock'] ? 'checked' : '' ?>> Sadece stoktakiler</label>
                        <label class="check"><input type="checkbox" name="indirim" value="1" <?= input('indirim') === '1' || current_path() === '/kampanyalar' ? 'checked' : '' ?> <?= current_path() === '/kampanyalar' ? 'disabled' : '' ?>> İndirimli ürünler</label>
                    </div>
                    <button class="btn btn-dark btn-block" type="submit"><?= icon('filter', '', 17) ?> Filtreleri Uygula</button>
                    <?php if ($hasFilters): ?><a href="<?= e(url(ltrim(current_path(), '/')) . ($filters['q'] !== '' ? '?q=' . rawurlencode($filters['q']) : '')) ?>" class="btn btn-ghost btn-block btn-sm mt-1">Filtreleri temizle</a><?php endif; ?>
                </div>
            </form>
        </aside>

        <div>
            <div class="toolbar">
                <div class="flex items-center gap-1">
                    <button class="btn btn-outline btn-sm filter-toggle" data-filters-open><?= icon('filter', '', 16) ?> Filtrele</button>
                    <span class="count"><strong><?= (int) $result['total'] ?></strong> ürün listeleniyor</span>
                </div>
                <div class="toolbar-right">
                    <select class="form-control" data-sort aria-label="Sırala">
                        <?php foreach ($sorts as $key => $label): ?>
                            <option value="<?= e(query_url(['sirala' => $key, 'sayfa' => null])) ?>" <?= $filters['sort'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($hasFilters): ?>
                <div class="active-filters">
                    <?php foreach ($filters['brands'] as $b): ?>
                        <a class="chip" href="<?= e(query_url(['marka' => array_values(array_diff($filters['brands'], [$b])), 'sayfa' => null])) ?>"><?= e($b) ?> <?= icon('x') ?></a>
                    <?php endforeach; ?>
                    <?php if ($filters['min'] !== '' || $filters['max'] !== ''): ?>
                        <a class="chip" href="<?= e(query_url(['min' => null, 'max' => null, 'sayfa' => null])) ?>"><?= e(($filters['min'] !== '' ? money($filters['min']) : '0') . ' – ' . ($filters['max'] !== '' ? money($filters['max']) : '∞')) ?> <?= icon('x') ?></a>
                    <?php endif; ?>
                    <?php if ($filters['in_stock']): ?><a class="chip" href="<?= e(query_url(['stok' => null, 'sayfa' => null])) ?>">Stoktakiler <?= icon('x') ?></a><?php endif; ?>
                    <?php if (input('indirim') === '1'): ?><a class="chip" href="<?= e(query_url(['indirim' => null, 'sayfa' => null])) ?>">İndirimli <?= icon('x') ?></a><?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($result['items']): ?>
                <?= partial('partials/product-grid', ['products' => $result['items'], 'cols' => 3]) ?>
                <?= partial('partials/pagination', ['result' => $result]) ?>
            <?php else: ?>
                <?= partial('partials/empty', [
                    'icon'   => 'search',
                    'title'  => 'Aradığınız kriterlere uygun ürün bulunamadı',
                    'text'   => 'Filtreleri değiştirerek veya farklı bir arama yaparak tekrar deneyebilirsiniz.',
                    'action' => ['Tüm ürünlere göz at', 'urunler'],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

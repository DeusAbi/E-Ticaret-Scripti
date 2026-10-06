<?php
/** @var array $sliders @var array $categories @var array $featured @var array $deals @var array $newArrivals @var array $bestsellers */
$tilePalettes = [['#eef2ff', '#3b5bdb'], ['#fff1ea', '#d9480f'], ['#ebfbee', '#2b8a3e'], ['#e3fafc', '#0c8599'], ['#fff0f6', '#c2255c'], ['#f3f0ff', '#6741d9']];
$firstLight = ($sliders[0]['theme'] ?? 'dark') === 'light';
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="slider<?= $firstLight ? ' is-light' : '' ?>" data-slider>
            <?php foreach ($sliders as $i => $s): ?>
                <div class="slide slide-<?= e($s['theme']) ?><?= $i === 0 ? ' is-active' : '' ?>" style="background:<?= e($s['bg_color']) ?>" data-theme="<?= e($s['theme']) ?>">
                    <div class="slide-content">
                        <?php if ($s['eyebrow']): ?><span class="slide-eyebrow"><?= icon('sparkles', '', 14) ?> <?= e($s['eyebrow']) ?></span><?php endif; ?>
                        <h2><?= e($s['title']) ?></h2>
                        <?php if ($s['subtitle']): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
                        <?php if ($s['button_text']): ?>
                            <a href="<?= e(url((string) $s['button_link'])) ?>" class="btn btn-lg <?= $s['theme'] === 'dark' ? 'btn-light' : 'btn-dark' ?>"><?= e($s['button_text']) ?> <?= icon('arrow-right', '', 18) ?></a>
                        <?php endif; ?>
                    </div>
                    <?php if ($s['image']): ?>
                        <div class="slide-media"><img src="<?= e(media($s['image'])) ?>" alt="" <?= $i === 0 ? '' : 'loading="lazy"' ?>></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (count($sliders) > 1): ?>
                <div class="slider-dots">
                    <?php foreach ($sliders as $i => $s): ?><button class="<?= $i === 0 ? 'is-active' : '' ?>" data-slide-to="<?= $i ?>" aria-label="Slayt <?= $i + 1 ?>"></button><?php endforeach; ?>
                </div>
                <div class="slider-arrows">
                    <button data-slide-prev aria-label="Önceki"><?= icon('arrow-left', '', 18) ?></button>
                    <button data-slide-next aria-label="Sonraki"><?= icon('arrow-right', '', 18) ?></button>
                </div>
            <?php endif; ?>
        </div>
        <div class="hero-side">
            <a href="<?= e(url('kampanyalar')) ?>" class="promo-card p1">
                <div><span class="eyebrow">Haftanın Fırsatları</span><h3>%40'a varan<br>indirimler</h3></div>
                <span class="more">Kampanyaları gör <?= icon('arrow-right', '', 16) ?></span>
                <svg class="deco" viewBox="0 0 24 24" fill="none" stroke="#ef5a24" stroke-opacity=".35" stroke-width="1.2"><?= App\Core\Icons::PATHS['tag'] ?></svg>
            </a>
            <a href="<?= e(url('urunler?sirala=newest')) ?>" class="promo-card p2">
                <div><span class="eyebrow">Yeni Gelenler</span><h3>Sezonun en<br>yeni ürünleri</h3></div>
                <span class="more">Keşfet <?= icon('arrow-right', '', 16) ?></span>
                <svg class="deco" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-opacity=".35" stroke-width="1.2"><?= App\Core\Icons::PATHS['sparkles'] ?></svg>
            </a>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature"><span class="feature-icon"><?= icon('truck', '', 22) ?></span><div><strong>Ücretsiz Kargo</strong><span><?= e(money(setting('free_shipping_threshold'))) ?> üzeri siparişlerde</span></div></div>
            <div class="feature"><span class="feature-icon"><?= icon('rotate', '', 22) ?></span><div><strong>Kolay İade</strong><span>14 gün içinde ücretsiz iade</span></div></div>
            <div class="feature"><span class="feature-icon"><?= icon('shield', '', 22) ?></span><div><strong>Güvenli Ödeme</strong><span>256-bit SSL koruması</span></div></div>
            <div class="feature"><span class="feature-icon"><?= icon('headphones', '', 22) ?></span><div><strong>7/24 Destek</strong><span>Uzman müşteri hizmetleri</span></div></div>
        </div>
    </div>
</section>

<?php if ($categories): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow"><?= icon('grid', '', 14) ?> Kategoriler</span><h2>Popüler kategorileri keşfedin</h2></div>
            <a href="<?= e(url('urunler')) ?>" class="view-all">Tüm ürünler <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <div class="cat-grid">
            <?php foreach (array_slice($categories, 0, 6) as $i => $c):
                $count = (int) $c['product_count'] + array_sum(array_map(static fn ($ch) => (int) $ch['product_count'], $c['children']));
                [$bg, $fg] = $tilePalettes[$i % count($tilePalettes)]; ?>
                <a href="<?= e(url('kategori/' . $c['slug'])) ?>" class="cat-tile" style="--tile-bg:<?= $bg ?>;--tile-fg:<?= $fg ?>">
                    <span class="cat-icon"><?= icon($c['icon'] ?: 'tag', '', 30, 1.7) ?></span>
                    <strong><?= e($c['name']) ?></strong>
                    <span><?= $count ?> ürün</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($featured): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow"><?= icon('award', '', 14) ?> Editörün Seçimi</span><h2>Öne çıkan ürünler</h2><p>Müşterilerimizin en çok beğendiği, özenle seçilmiş ürünler.</p></div>
            <a href="<?= e(url('urunler')) ?>" class="view-all">Tümünü gör <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <?= partial('partials/product-grid', ['products' => $featured]) ?>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="deal-banner">
            <div>
                <span class="slide-eyebrow"><?= icon('gift', '', 14) ?> Yeni Üyelere Özel</span>
                <h2>İlk siparişinize özel %10 indirim</h2>
                <p>Hemen üye olun, sepette kupon kodunu kullanarak ilk alışverişinizde anında indirim kazanın.</p>
                <div class="deal-code">HOSGELDIN10 <button type="button" data-copy="HOSGELDIN10"><?= icon('copy', '', 14) ?> Kopyala</button></div>
            </div>
            <div class="countdown" data-countdown="<?= e(date('Y-m-d\T23:59:59')) ?>">
                <div><strong data-h>00</strong><span>Saat</span></div>
                <div><strong data-m>00</strong><span>Dakika</span></div>
                <div><strong data-s>00</strong><span>Saniye</span></div>
            </div>
        </div>
    </div>
</section>

<?php if ($deals): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow"><?= icon('zap', '', 14) ?> Fırsat Köşesi</span><h2>Kaçırılmayacak indirimler</h2><p>Sınırlı stoklarla, en yüksek indirim oranına sahip ürünler.</p></div>
            <a href="<?= e(url('kampanyalar')) ?>" class="view-all">Tüm kampanyalar <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <?= partial('partials/product-grid', ['products' => $deals]) ?>
    </div>
</section>
<?php endif; ?>

<?php if ($newArrivals): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow"><?= icon('sparkles', '', 14) ?> Yeni Gelenler</span><h2>Mağazaya yeni eklenenler</h2><p>Koleksiyonumuza en son katılan ürünleri ilk siz keşfedin.</p></div>
            <a href="<?= e(url('urunler?sirala=newest')) ?>" class="view-all">Tümünü gör <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <?= partial('partials/product-grid', ['products' => $newArrivals]) ?>
    </div>
</section>
<?php endif; ?>

<?php if ($bestsellers): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow"><?= icon('trending', '', 14) ?> Çok Satanlar</span><h2>En çok tercih edilenler</h2></div>
            <a href="<?= e(url('urunler?sirala=bestseller')) ?>" class="view-all">Tümünü gör <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <?= partial('partials/product-grid', ['products' => $bestsellers]) ?>
    </div>
</section>
<?php endif; ?>

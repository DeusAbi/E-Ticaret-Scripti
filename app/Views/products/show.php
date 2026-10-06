<?php
/** @var array $product @var array $images @var array $specs @var array $reviews @var array $distribution @var array $related */
use App\Services\Pricing;
use App\Services\Product;

$discount = Pricing::discountPercent((float) $product['price'], $product['compare_price'] !== null ? (float) $product['compare_price'] : null);
$stock = Product::stockState($product);
$rating = (float) ($product['rating_avg'] ?? 0);
$crumbs = [];
foreach ($breadcrumbs as $b) {
    $crumbs[] = [$b['name'], 'kategori/' . $b['slug']];
}
$crumbs[] = [str_limit($product['name'], 50), null];
$taxRate = (int) $product['tax_rate'];
?>
<div class="container">
    <div class="page-head"><?= partial('partials/breadcrumbs', ['items' => $crumbs]) ?></div>

    <div class="pdp">
        <div class="gallery" data-gallery>
            <div class="gallery-thumbs">
                <?php foreach ($images as $i => $img): ?>
                    <button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>" data-src="<?= e(media($img['path'])) ?>" aria-label="Görsel <?= $i + 1 ?>">
                        <img src="<?= e(media($img['path'])) ?>" alt="" loading="lazy">
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="gallery-main" data-zoom>
                <img src="<?= e(media($images[0]['path'] ?? null)) ?>" alt="<?= e($product['name']) ?>" data-main-image>
                <div class="product-badges">
                    <?php if ($discount > 0): ?><span class="tag tag-sale">%<?= $discount ?> İndirim</span><?php endif; ?>
                    <?php if ((int) $product['is_featured']): ?><span class="tag tag-new">Öne Çıkan</span><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="pdp-info">
            <?php if ($product['brand']): ?>
                <a class="brand" href="<?= e(url('urunler?marka[]=' . rawurlencode($product['brand']))) ?>"><?= icon('award', '', 16) ?> <?= e($product['brand']) ?></a>
            <?php endif; ?>
            <h1><?= e($product['name']) ?></h1>
            <div class="pdp-meta">
                <?php if ($rating > 0): ?>
                    <a href="#yorumlar" data-tab-link="reviews" class="flex items-center gap-1"><?= stars($rating, 16) ?> <strong style="color:var(--ink)"><?= number_format($rating, 1, ',', '') ?></strong> (<?= (int) $product['rating_count'] ?> değerlendirme)</a>
                    <span class="sep"></span>
                <?php endif; ?>
                <?php if ($product['sku']): ?><span>Stok Kodu: <strong><?= e($product['sku']) ?></strong></span><span class="sep"></span><?php endif; ?>
                <span><?= (int) $product['sales_count'] ?>+ satış</span>
            </div>

            <div class="pdp-price">
                <span class="now<?= $discount ? ' is-sale' : '' ?>"><?= e(money($product['price'])) ?></span>
                <?php if ($discount): ?>
                    <span class="old"><?= e(money($product['compare_price'])) ?></span>
                    <span class="save"><?= e(money((float) $product['compare_price'] - (float) $product['price'])) ?> tasarruf</span>
                <?php endif; ?>
                <span class="tax">Fiyatlara %<?= $taxRate ?> KDV dahildir.</span>
            </div>

            <?php if ($product['short_description']): ?><p class="pdp-short"><?= e($product['short_description']) ?></p><?php endif; ?>

            <div class="stock-pill stock-<?= e($stock['key']) ?>">
                <?= $stock['key'] === 'in' ? 'Stokta — hemen kargoda' : ($stock['key'] === 'low' ? 'Tükenmek üzere! ' . e($stock['label']) : 'Bu ürün şu anda stokta yok') ?>
            </div>

            <form action="<?= e(url('sepet/ekle')) ?>" method="post" data-add-form>
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <div class="buy-row">
                    <div class="qty" data-qty>
                        <button type="button" data-qty-minus aria-label="Azalt"><?= icon('minus', '', 18) ?></button>
                        <input type="number" name="qty" value="1" min="1" max="<?= max(1, (int) $product['stock']) ?>" aria-label="Adet">
                        <button type="button" data-qty-plus aria-label="Arttır"><?= icon('plus', '', 18) ?></button>
                    </div>
                    <button type="submit" class="btn btn-primary" <?= $stock['key'] === 'out' ? 'disabled' : '' ?>><?= icon('bag', '', 19) ?> <?= $stock['key'] === 'out' ? 'Stokta Yok' : 'Sepete Ekle' ?></button>
                    <button type="button" class="btn btn-outline wish-lg<?= $inWishlist ? ' is-active' : '' ?>" data-wishlist="<?= (int) $product['id'] ?>" aria-label="Favorilere ekle" title="Favorilere ekle"><?= icon('heart', '', 20) ?></button>
                </div>
                <?php if ($stock['key'] !== 'out'): ?>
                    <button type="submit" name="buy_now" value="1" class="btn btn-dark btn-block btn-lg"><?= icon('zap', '', 18) ?> Hemen Satın Al</button>
                <?php endif; ?>
            </form>

            <div class="pdp-perks">
                <div class="perk"><?= icon('truck', '', 22) ?><div><strong><?= (float) $product['price'] >= (float) setting('free_shipping_threshold') ? 'Ücretsiz Kargo' : 'Hızlı Kargo' ?></strong><span><?= e(setting('shipping_days')) ?> içinde kargoda</span></div></div>
                <div class="perk"><?= icon('rotate', '', 22) ?><div><strong>14 Gün İade</strong><span>Koşulsuz ücretsiz iade</span></div></div>
                <div class="perk"><?= icon('shield', '', 22) ?><div><strong>Güvenli Ödeme</strong><span>Taksit seçenekleriyle</span></div></div>
                <div class="perk"><?= icon('award', '', 22) ?><div><strong>Orijinal Ürün</strong><span>Faturalı & garantili</span></div></div>
            </div>
        </div>
    </div>

    <div class="tabs" data-tabs id="yorumlar">
        <div class="tab-nav" role="tablist">
            <button class="is-active" data-tab="description" role="tab">Ürün Açıklaması</button>
            <?php if ($specs): ?><button data-tab="specs" role="tab">Teknik Özellikler</button><?php endif; ?>
            <button data-tab="reviews" role="tab">Değerlendirmeler <span class="count"><?= count($reviews) ?></span></button>
            <button data-tab="shipping" role="tab">Teslimat & İade</button>
        </div>
        <div class="tab-panel is-active" data-panel="description">
            <div class="prose"><?= rich_text($product['description'] ?: $product['short_description']) ?></div>
        </div>
        <?php if ($specs): ?>
            <div class="tab-panel" data-panel="specs">
                <table class="spec-table">
                    <?php foreach ($specs as $k => $v): ?><tr><th><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
        <div class="tab-panel" data-panel="reviews">
            <div class="reviews-grid">
                <div class="rating-summary">
                    <div class="big"><?= number_format($rating, 1, ',', '') ?></div>
                    <?= stars($rating, 20) ?>
                    <div class="small text-muted"><?= count($reviews) ?> değerlendirme</div>
                    <div class="rating-bars">
                        <?php $total = max(1, count($reviews)); for ($s = 5; $s >= 1; $s--): ?>
                            <div class="rating-bar"><span><?= $s ?> ★</span><span class="bar"><i style="width:<?= round($distribution[$s] / $total * 100) ?>%"></i></span><span><?= $distribution[$s] ?></span></div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div>
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="review">
                                <div class="review-head">
                                    <span class="avatar"><?= e(initials($r['author_name'])) ?></span>
                                    <div>
                                        <strong><?= e(mb_substr($r['author_name'], 0, 1) . '*** ' . mb_substr((string) (explode(' ', $r['author_name'])[1] ?? ''), 0, 1) . '***') ?></strong>
                                        <span><?= e(date_tr($r['created_at'], false)) ?> · <span class="verified"><?= icon('check-circle', '', 13) ?> Doğrulanmış alıcı</span></span>
                                    </div>
                                    <span style="margin-left:auto"><?= stars((float) $r['rating'], 14) ?></span>
                                </div>
                                <?php if ($r['title']): ?><h5><?= e($r['title']) ?></h5><?php endif; ?>
                                <p><?= nl2br(e($r['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Bu ürün için henüz değerlendirme yapılmamış. İlk değerlendirmeyi siz yapın!</p>
                    <?php endif; ?>

                    <?php if (!auth()): ?>
                        <div class="review-form"><p class="mb-0">Değerlendirme yapmak için <a class="link" href="<?= e(url('giris?redirect=' . rawurlencode('/urun/' . $product['slug']))) ?>">giriş yapın</a>.</p></div>
                    <?php elseif ($canReview): ?>
                        <form class="review-form" action="<?= e(url('urun/' . $product['slug'] . '/yorum')) ?>" method="post">
                            <?= csrf_field() ?>
                            <h3>Ürünü değerlendirin</h3>
                            <div class="form-group">
                                <label class="form-label">Puanınız <span class="req">*</span></label>
                                <div class="rating-input">
                                    <?php for ($s = 5; $s >= 1; $s--): ?>
                                        <input type="radio" name="rating" id="r<?= $s ?>" value="<?= $s ?>" <?= (int) old('rating', 5) === $s ? 'checked' : '' ?>><label for="r<?= $s ?>" title="<?= $s ?> yıldız"><?= icon('star') ?></label>
                                    <?php endfor; ?>
                                </div>
                                <?= field_error('rating') ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="rv-title">Başlık</label>
                                <input class="form-control" id="rv-title" name="title" maxlength="120" value="<?= e(old('title')) ?>" placeholder="Kısaca özetleyin">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="rv-comment">Yorumunuz <span class="req">*</span></label>
                                <textarea class="form-control<?= has_error('comment') ?>" id="rv-comment" name="comment" rows="4" required minlength="10" placeholder="Ürünle ilgili deneyiminizi paylaşın"><?= e(old('comment')) ?></textarea>
                                <?= field_error('comment') ?>
                            </div>
                            <button class="btn btn-dark" type="submit"><?= icon('send', '', 17) ?> Değerlendirmeyi Gönder</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="tab-panel" data-panel="shipping">
            <div class="prose">
                <h3>Teslimat</h3>
                <p>Siparişleriniz <?= e(setting('shipping_days')) ?> içinde kargoya teslim edilir. <?= e(money(setting('free_shipping_threshold'))) ?> ve üzeri alışverişlerde kargo ücretsizdir; altındaki siparişlerde kargo ücreti <?= e(money(setting('shipping_fee'))) ?>'dir.</p>
                <h3>İade</h3>
                <p>Ürünü teslim aldığınız tarihten itibaren 14 gün içinde ücretsiz olarak iade edebilirsiniz. Detaylı bilgi için <a class="link" href="<?= e(url('sayfa/teslimat-ve-iade')) ?>">Teslimat ve İade</a> sayfamızı inceleyin.</p>
            </div>
        </div>
    </div>

    <?php if ($related): ?>
        <section class="section">
            <div class="section-head"><div><span class="eyebrow"><?= icon('layers', '', 14) ?> Benzer Ürünler</span><h2>Bunlar da ilginizi çekebilir</h2></div></div>
            <?= partial('partials/product-grid', ['products' => $related]) ?>
        </section>
    <?php endif; ?>
</div>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'sku' => $product['sku'],
    'brand' => ['@type' => 'Brand', 'name' => $product['brand']],
    'description' => $product['short_description'],
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'TRY',
        'price' => number_format((float) $product['price'], 2, '.', ''),
        'availability' => $stock['key'] === 'out' ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
    ],
] + ($rating > 0 ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => round($rating, 1), 'reviewCount' => (int) $product['rating_count']]] : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>

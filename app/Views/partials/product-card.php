<?php
/** @var array $p */
use App\Services\Pricing;
use App\Services\Product;

$discount = Pricing::discountPercent((float) $p['price'], $p['compare_price'] !== null ? (float) $p['compare_price'] : null);
$stock = Product::stockState($p);
$isNew = strtotime($p['created_at']) > strtotime('-14 days');
$rating = (float) ($p['rating_avg'] ?? 0);
$wished = in_array((int) $p['id'], $wishlistIds ?? [], true);
$link = url('urun/' . $p['slug']);
?>
<article class="product-card<?= $stock['key'] === 'out' ? ' is-out' : '' ?>">
    <a href="<?= e($link) ?>" class="product-media" aria-label="<?= e($p['name']) ?>">
        <img src="<?= e(media($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="400" height="400">
        <?php if (!empty($p['image_alt'])): ?>
            <img class="img-alt" src="<?= e(media($p['image_alt'])) ?>" alt="" loading="lazy" width="400" height="400">
        <?php endif; ?>
    </a>
    <div class="product-badges">
        <?php if ($stock['key'] === 'out'): ?>
            <span class="tag tag-out">Tükendi</span>
        <?php else: ?>
            <?php if ($discount > 0): ?><span class="tag tag-sale">%<?= $discount ?> İndirim</span><?php endif; ?>
            <?php if ($isNew): ?><span class="tag tag-new">Yeni</span><?php endif; ?>
            <?php if ($stock['key'] === 'low'): ?><span class="tag tag-low"><?= e($stock['label']) ?></span><?php endif; ?>
        <?php endif; ?>
    </div>
    <button class="wish-btn<?= $wished ? ' is-active' : '' ?>" data-wishlist="<?= (int) $p['id'] ?>" aria-label="Favorilere ekle" title="Favorilere ekle"><?= icon('heart', '', 18) ?></button>
    <div class="product-body">
        <?php if (!empty($p['brand'])): ?><div class="product-cat"><?= e($p['brand']) ?></div><?php endif; ?>
        <h3 class="product-title"><a href="<?= e($link) ?>"><?= e($p['name']) ?></a></h3>
        <div class="product-rating">
            <?php if ($rating > 0): ?>
                <?= stars($rating, 13) ?> <span><?= number_format($rating, 1, ',', '') ?> (<?= (int) $p['rating_count'] ?>)</span>
            <?php else: ?>
                <span>Henüz değerlendirilmedi</span>
            <?php endif; ?>
        </div>
        <div class="product-foot">
            <div class="price">
                <?php if ($discount > 0): ?><span class="price-old"><?= e(money($p['compare_price'])) ?></span><?php endif; ?>
                <span class="price-now<?= $discount > 0 ? ' is-sale' : '' ?>"><?= e(money($p['price'])) ?></span>
            </div>
            <button class="add-btn" data-add-to-cart="<?= (int) $p['id'] ?>" <?= $stock['key'] === 'out' ? 'disabled' : '' ?> aria-label="Sepete ekle" title="<?= $stock['key'] === 'out' ? 'Stokta yok' : 'Sepete ekle' ?>">
                <?= icon($stock['key'] === 'out' ? 'x' : 'plus', '', 20) ?>
            </button>
        </div>
    </div>
</article>

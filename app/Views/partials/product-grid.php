<?php /** @var array $products */ $wishlistIds = wishlist_ids(); ?>
<div class="product-grid<?= isset($cols) ? ' cols-' . (int) $cols : '' ?>">
    <?php foreach ($products as $p): ?>
        <?= partial('partials/product-card', ['p' => $p, 'wishlistIds' => $wishlistIds]) ?>
    <?php endforeach; ?>
</div>

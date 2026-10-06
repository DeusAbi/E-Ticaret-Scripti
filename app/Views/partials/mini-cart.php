<?php /** @var array|null $summary */ ?>
<?php if (!$summary || !$summary['items']): ?>
    <div class="mini-cart-empty">
        <?= icon('bag', '', 40, 1.5) ?>
        <strong style="display:block;color:var(--ink)">Sepetiniz boş</strong>
        <span class="small">Beğendiğiniz ürünleri sepete ekleyin.</span>
    </div>
<?php else: ?>
    <div class="mini-cart-head"><strong>Sepetim</strong><span class="small text-muted"><?= (int) $summary['count'] ?> ürün</span></div>
    <div class="mini-cart-items">
        <?php foreach ($summary['items'] as $item): ?>
            <a class="mini-cart-item" href="<?= e(url('urun/' . $item['product']['slug'])) ?>">
                <img src="<?= e(media($item['product']['image'])) ?>" alt="" loading="lazy">
                <div>
                    <div class="name"><?= e($item['product']['name']) ?></div>
                    <div class="meta"><?= (int) $item['qty'] ?> × <?= e(money($item['price'])) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="mini-cart-foot">
        <div class="row"><span>Toplam</span><strong><?= e(money($summary['total'])) ?></strong></div>
        <div class="btns">
            <a href="<?= e(url('sepet')) ?>" class="btn btn-outline btn-sm">Sepete Git</a>
            <a href="<?= e(url('odeme')) ?>" class="btn btn-primary btn-sm">Satın Al</a>
        </div>
    </div>
<?php endif; ?>

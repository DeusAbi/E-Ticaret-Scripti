<?php /** @var array $summary */ ?>
<?php if (!$summary['items']): ?>
    <div style="padding:16px 0 48px">
        <?= partial('partials/empty', [
            'icon'   => 'bag',
            'title'  => 'Sepetiniz şu anda boş',
            'text'   => 'Binlerce ürün arasından beğendiklerinizi sepetinize ekleyerek alışverişe başlayabilirsiniz.',
            'action' => ['Alışverişe Başla', 'urunler'],
        ]) ?>
    </div>
<?php else: ?>
<div class="cart-layout">
    <div class="card">
        <div class="card-head">
            <h3><?= icon('bag', '', 20) ?> Sepetimdeki Ürünler <span class="badge"><?= (int) $summary['count'] ?> ürün</span></h3>
        </div>
        <?php if ($summary['free_threshold'] > 0): ?>
            <?php $pct = min(100, round(($summary['subtotal'] - $summary['discount']) / $summary['free_threshold'] * 100)); ?>
            <div class="ship-progress<?= $summary['free_remaining'] <= 0 ? ' is-free' : '' ?>">
                <?php if ($summary['free_remaining'] > 0): ?>
                    <?= icon('truck', '', 16) ?> Ücretsiz kargo için <strong><?= e(money($summary['free_remaining'])) ?></strong> değerinde daha ürün ekleyin.
                <?php else: ?>
                    <?= icon('check-circle', '', 16) ?> Tebrikler! Siparişiniz <strong>ücretsiz kargo</strong> kapsamında.
                <?php endif; ?>
                <div class="bar"><i style="width:<?= $pct ?>%"></i></div>
            </div>
        <?php endif; ?>
        <ul class="cart-items" data-cart-items>
            <?php foreach ($summary['items'] as $item): $p = $item['product']; ?>
                <li class="cart-item">
                    <a href="<?= e(url('urun/' . $p['slug'])) ?>"><img src="<?= e(media($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
                    <div class="info">
                        <a class="name" href="<?= e(url('urun/' . $p['slug'])) ?>"><?= e($p['name']) ?></a>
                        <div class="meta"><?= $p['brand'] ? e($p['brand']) . ' · ' : '' ?><?= (int) $p['stock'] <= (int) $p['low_stock_threshold'] ? '<span style="color:var(--warning)">Son ' . (int) $p['stock'] . ' ürün</span>' : '<span class="text-success">Stokta</span>' ?></div>
                        <div class="unit">Birim fiyat: <?= e(money($item['price'])) ?></div>
                    </div>
                    <div class="qty qty-sm" data-cart-qty="<?= (int) $item['id'] ?>">
                        <button type="button" data-qty-minus aria-label="Azalt"><?= icon('minus', '', 16) ?></button>
                        <input type="number" value="<?= (int) $item['qty'] ?>" min="1" max="<?= (int) $p['stock'] ?>" aria-label="Adet">
                        <button type="button" data-qty-plus aria-label="Arttır"><?= icon('plus', '', 16) ?></button>
                    </div>
                    <div class="line-total"><?= e(money($item['total'])) ?></div>
                    <button class="remove" type="button" data-cart-remove="<?= (int) $item['id'] ?>" aria-label="Sepetten çıkar" title="Sepetten çıkar"><?= icon('trash', '', 18) ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <aside class="summary">
        <div class="card card-body">
            <h3 class="mb-2">Sipariş Özeti</h3>
            <?= partial('cart/summary-rows', ['summary' => $summary]) ?>
            <a href="<?= e(url('odeme')) ?>" class="btn btn-primary btn-block btn-lg mt-3">Sepeti Onayla <?= icon('arrow-right', '', 18) ?></a>

            <div class="coupon-box">
                <?php if ($summary['coupon']): ?>
                    <div class="coupon-applied">
                        <span><?= icon('tag', '', 16) ?> <strong><?= e($summary['coupon']['code']) ?></strong> uygulandı</span>
                        <form action="<?= e(url('sepet/kupon/kaldir')) ?>" method="post"><?= csrf_field() ?><button type="submit">Kaldır</button></form>
                    </div>
                <?php else: ?>
                    <form action="<?= e(url('sepet/kupon')) ?>" method="post">
                        <?= csrf_field() ?>
                        <label class="form-label" for="coupon">İndirim kuponu</label>
                        <div class="input-group">
                            <input class="form-control" id="coupon" name="code" placeholder="Kupon kodu" required>
                            <button class="btn btn-dark" type="submit">Uygula</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="secure-note"><?= icon('shield', '', 16) ?> Güvenli alışveriş · 14 gün iade</div>
        </div>
        <a href="<?= e(url('urunler')) ?>" class="btn btn-ghost btn-block mt-1"><?= icon('arrow-left', '', 17) ?> Alışverişe devam et</a>
    </aside>
</div>
<?php endif; ?>

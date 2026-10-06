<aside class="auth-aside">
    <span class="ring"></span><span class="ring r2"></span>
    <?= partial('partials/logo') ?>
    <div>
        <h2><?= e($heading ?? 'Alışverişin en şık hali burada.') ?></h2>
        <p><?= e($sub ?? 'Binlerce ürün, güvenli ödeme ve hızlı teslimat ile tanışın.') ?></p>
        <div class="auth-perks">
            <div><span class="ic"><?= icon('truck', '', 20) ?></span><div><strong>Hızlı & ücretsiz kargo</strong><span><?= e(money(setting('free_shipping_threshold'))) ?> üzeri siparişlerde</span></div></div>
            <div><span class="ic"><?= icon('shield', '', 20) ?></span><div><strong>Güvenli alışveriş</strong><span>256-bit SSL ile korunan ödeme</span></div></div>
            <div><span class="ic"><?= icon('gift', '', 20) ?></span><div><strong>Üyelere özel fırsatlar</strong><span>İlk siparişe %10 indirim</span></div></div>
        </div>
    </div>
    <small style="color:rgba(255,255,255,.5)">© <?= date('Y') ?> <?= e(setting('store_name')) ?></small>
</aside>

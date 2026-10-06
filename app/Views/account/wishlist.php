<?php ob_start(); ?>
<div class="page-title mb-2"><h1>Favorilerim</h1><span class="text-muted"><?= count($items) ?> ürün</span></div>
<?php if ($items): ?>
    <?= partial('partials/product-grid', ['products' => $items, 'cols' => 3]) ?>
<?php else: ?>
    <?= partial('partials/empty', ['icon' => 'heart', 'title' => 'Favori listeniz boş', 'text' => 'Beğendiğiniz ürünlerdeki kalp simgesine tıklayarak favorilerinize ekleyebilirsiniz.', 'action' => ['Ürünleri Keşfet', 'urunler']]) ?>
<?php endif; ?>
<?php $inner = ob_get_clean(); echo partial('account/layout', ['inner' => $inner, 'crumb' => 'Favorilerim']); ?>

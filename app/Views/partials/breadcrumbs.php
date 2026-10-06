<?php /** @var array $items [[label, url|null], ...] */ ?>
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= e(url('/')) ?>">Ana Sayfa</a>
    <?php foreach ($items as [$label, $href]): ?>
        <?= icon('chevron-right', '', 14) ?>
        <?php if ($href): ?><a href="<?= e(url($href)) ?>"><?= e($label) ?></a><?php else: ?><span><?= e($label) ?></span><?php endif; ?>
    <?php endforeach; ?>
</nav>

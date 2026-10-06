<?php $logo = setting('store_logo'); $name = (string) setting('store_name', 'Vitrin'); ?>
<a href="<?= e(url('/')) ?>" class="logo" aria-label="<?= e($name) ?> ana sayfa">
    <?php if ($logo): ?>
        <img src="<?= e(media($logo)) ?>" alt="<?= e($name) ?>">
    <?php else: ?>
        <span class="logo-mark"><?= icon('bag', '', 21, 2.2) ?></span>
        <span><?= e($name) ?><span class="logo-dot">.</span></span>
    <?php endif; ?>
</a>

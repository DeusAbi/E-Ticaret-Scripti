<?php /** @var string $title @var ?string $sub @var array $crumbs @var string $actions */ ?>
<div class="admin-head">
    <div>
        <?php if (!empty($crumbs)): ?>
            <div class="admin-crumbs">
                <?php foreach ($crumbs as $i => [$label, $href]): ?>
                    <?php if ($i): ?><?= icon('chevron-right') ?><?php endif; ?>
                    <?php if ($href): ?><a href="<?= e(url($href)) ?>"><?= e($label) ?></a><?php else: ?><span><?= e($label) ?></span><?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <h1><?= e($title) ?></h1>
        <?php if (!empty($sub)): ?><p><?= e($sub) ?></p><?php endif; ?>
    </div>
    <?php if (!empty($actions)): ?><div class="actions"><?= $actions ?></div><?php endif; ?>
</div>

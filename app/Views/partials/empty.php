<div class="empty">
    <div class="empty-icon"><?= icon($icon ?? 'package', '', 36, 1.6) ?></div>
    <h3><?= e($title) ?></h3>
    <p><?= e($text ?? '') ?></p>
    <?php if (!empty($action)): ?>
        <a href="<?= e(url($action[1])) ?>" class="btn btn-primary"><?= e($action[0]) ?></a>
    <?php endif; ?>
</div>

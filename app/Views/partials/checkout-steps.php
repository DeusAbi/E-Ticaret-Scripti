<?php $current = $current ?? 2; $steps = [1 => 'Sepet', 2 => 'Teslimat & Ödeme', 3 => 'Onay']; ?>
<div class="steps">
    <?php foreach ($steps as $n => $label): ?>
        <?php if ($n > 1): ?><span class="step-line<?= $n <= $current ? ' is-done' : '' ?>"></span><?php endif; ?>
        <div class="step <?= $n < $current || $current === 3 ? 'is-done' : ($n === $current ? 'is-current' : '') ?>">
            <span class="n"><?= $n < $current || $current === 3 ? icon('check', '', 15, 3) : $n ?></span><span class="lbl"><?= e($label) ?></span>
        </div>
    <?php endforeach; ?>
</div>

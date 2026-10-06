<?php /** @var array $result */ ?>
<div class="admin-pagination">
    <span>Toplam <strong><?= (int) $result['total'] ?></strong> kayıt<?= $result['pages'] > 1 ? ' · Sayfa ' . $result['page'] . ' / ' . $result['pages'] : '' ?></span>
    <?= partial('partials/pagination', ['result' => $result]) ?>
</div>

<?= partial('admin/partials-head', [
    'title' => 'Kategoriler',
    'sub' => 'Mağaza menüsünde görünen kategori yapısını düzenleyin.',
    'actions' => '<a href="' . e(url('admin/kategoriler/yeni')) . '" class="btn btn-primary">' . icon('plus', '', 17) . ' Yeni Kategori</a>',
]) ?>
<?php
$rowsHtml = static function (array $c, bool $child) {
    $count = (int) $c['product_count'];
    ob_start(); ?>
    <li class="cat-row<?= $child ? ' child' : '' ?>">
        <span class="cat-ic"><?= icon($c['icon'] ?: 'tag', '', $child ? 16 : 20) ?></span>
        <div>
            <div class="name"><?= e($c['name']) ?> <?= (int) $c['is_active'] ? '' : badge('Gizli', 'muted') ?></div>
            <div class="meta">/kategori/<?= e($c['slug']) ?> · <?= $count ?> aktif ürün · Sıra: <?= (int) $c['sort_order'] ?></div>
        </div>
        <div class="actions">
            <a href="<?= e(url('kategori/' . $c['slug'])) ?>" target="_blank" class="btn btn-ghost btn-icon btn-sm" title="Mağazada gör"><?= icon('eye', '', 16) ?></a>
            <a href="<?= e(url('admin/kategoriler/' . $c['id'] . '/duzenle')) ?>" class="btn btn-outline btn-icon btn-sm" title="Düzenle"><?= icon('edit', '', 16) ?></a>
            <form action="<?= e(url('admin/kategoriler/' . $c['id'] . '/sil')) ?>" method="post" data-confirm="“<?= e($c['name']) ?>” kategorisini silmek istediğinize emin misiniz?"><?= csrf_field() ?><button class="btn btn-ghost btn-icon btn-sm text-danger" title="Sil"><?= icon('trash', '', 16) ?></button></form>
        </div>
    </li>
    <?php return ob_get_clean();
};
$inTree = [];
?>
<div class="panel">
    <ul class="cat-tree">
        <?php foreach ($tree as $root): $inTree[] = (int) $root['id']; ?>
            <?= $rowsHtml($root, false) ?>
            <?php foreach ($root['children'] as $ch): $inTree[] = (int) $ch['id']; ?><?= $rowsHtml($ch, true) ?><?php endforeach; ?>
        <?php endforeach; ?>
        <?php foreach ($all as $c): if (!in_array((int) $c['id'], $inTree, true)): ?><?= $rowsHtml($c, true) ?><?php endif; endforeach; ?>
        <?php if (!$all): ?><li class="cat-row text-muted">Henüz kategori eklenmemiş.</li><?php endif; ?>
    </ul>
</div>

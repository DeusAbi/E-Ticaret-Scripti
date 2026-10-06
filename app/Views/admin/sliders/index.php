<?= partial('admin/partials-head', [
    'title' => 'Ana Sayfa Slider', 'sub' => 'Ana sayfanın üst bölümündeki kampanya slaytlarını yönetin.',
    'actions' => '<a href="' . e(url('admin/slider/yeni')) . '" class="btn btn-primary">' . icon('plus', '', 17) . ' Yeni Slayt</a>',
]) ?>
<div class="panel">
    <?php foreach ($sliders as $s): ?>
        <div class="cat-row">
            <div class="slide-preview" style="background:<?= e($s['bg_color']) ?>;color:<?= $s['theme'] === 'dark' ? '#fff' : 'var(--ink)' ?>;flex:1;max-width:560px">
                <?php if ($s['image']): ?><img src="<?= e(media($s['image'])) ?>" alt=""><?php endif; ?>
                <div><small><?= e($s['eyebrow']) ?></small><strong><?= e($s['title']) ?></strong><small><?= e(str_limit($s['subtitle'], 80)) ?></small></div>
            </div>
            <div class="meta">Sıra: <?= (int) $s['sort_order'] ?><br><?= (int) $s['is_active'] ? badge('Yayında', 'success') : badge('Gizli', 'muted') ?></div>
            <div class="actions">
                <a href="<?= e(url('admin/slider/' . $s['id'] . '/duzenle')) ?>" class="btn btn-outline btn-icon btn-sm" title="Düzenle"><?= icon('edit', '', 16) ?></a>
                <form action="<?= e(url('admin/slider/' . $s['id'] . '/sil')) ?>" method="post" data-confirm="Slaytı silmek istiyor musunuz?"><?= csrf_field() ?><button class="btn btn-ghost btn-icon btn-sm text-danger" title="Sil"><?= icon('trash', '', 16) ?></button></form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$sliders): ?><div class="panel-body text-muted">Henüz slayt eklenmemiş.</div><?php endif; ?>
</div>

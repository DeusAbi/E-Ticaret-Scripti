<?= partial('admin/partials-head', [
    'title' => 'Sayfalar', 'sub' => 'Hakkımızda, sözleşmeler ve diğer kurumsal sayfalar.',
    'actions' => '<a href="' . e(url('admin/sayfalar/yeni')) . '" class="btn btn-primary">' . icon('plus', '', 17) . ' Yeni Sayfa</a>',
]) ?>
<div class="panel">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Başlık</th><th>Adres</th><th>Footer</th><th>Durum</th><th>Güncelleme</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pages as $p): ?>
                <tr>
                    <td><a class="link-strong" href="<?= e(url('admin/sayfalar/' . $p['id'] . '/duzenle')) ?>"><?= e($p['title']) ?></a></td>
                    <td class="small text-muted">/sayfa/<?= e($p['slug']) ?></td>
                    <td><?= (int) $p['show_in_footer'] ? icon('check', 'text-success', 18) : '—' ?></td>
                    <td><?= (int) $p['is_active'] ? badge('Yayında', 'success') : badge('Gizli', 'muted') ?></td>
                    <td class="small"><?= e(time_ago($p['updated_at'] ?? $p['created_at'])) ?></td>
                    <td><div class="actions">
                        <a class="btn btn-ghost btn-icon btn-sm" target="_blank" href="<?= e(url('sayfa/' . $p['slug'])) ?>" title="Görüntüle"><?= icon('eye', '', 16) ?></a>
                        <a class="btn btn-outline btn-icon btn-sm" href="<?= e(url('admin/sayfalar/' . $p['id'] . '/duzenle')) ?>" title="Düzenle"><?= icon('edit', '', 16) ?></a>
                        <form action="<?= e(url('admin/sayfalar/' . $p['id'] . '/sil')) ?>" method="post" data-confirm="Sayfayı silmek istiyor musunuz?"><?= csrf_field() ?><button class="btn btn-ghost btn-icon btn-sm text-danger" title="Sil"><?= icon('trash', '', 16) ?></button></form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= partial('admin/partials-head', ['title' => 'Ürün Yorumları', 'sub' => 'Müşteri değerlendirmelerini onaylayın veya kaldırın.']) ?>
<div class="seg">
    <a href="<?= e(url('admin/yorumlar')) ?>" class="<?= $filter === '' ? 'is-active' : '' ?>">Tümü</a>
    <a href="<?= e(url('admin/yorumlar?durum=bekleyen')) ?>" class="<?= $filter === 'bekleyen' ? 'is-active' : '' ?>">Onay Bekleyen <span class="n"><?= $pending ?></span></a>
    <a href="<?= e(url('admin/yorumlar?durum=onayli')) ?>" class="<?= $filter === 'onayli' ? 'is-active' : '' ?>">Yayında</a>
</div>
<div class="panel">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ürün</th><th>Müşteri</th><th>Puan</th><th style="width:40%">Yorum</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($result['items'] as $r): ?>
                <tr>
                    <td><?php if ($r['product_slug']): ?><a class="link-strong" href="<?= e(url('urun/' . $r['product_slug'])) ?>" target="_blank"><?= e(str_limit($r['product_name'], 40)) ?></a><?php else: ?>—<?php endif; ?></td>
                    <td><?= e($r['author_name']) ?><div class="small text-muted"><?= e(time_ago($r['created_at'])) ?></div></td>
                    <td><?= stars((float) $r['rating'], 13) ?></td>
                    <td><?php if ($r['title']): ?><strong style="color:var(--ink)"><?= e($r['title']) ?></strong><br><?php endif; ?><span class="small"><?= e(str_limit($r['comment'], 220)) ?></span></td>
                    <td><?= (int) $r['is_approved'] ? badge('Yayında', 'success') : badge('Bekliyor', 'warning') ?></td>
                    <td><div class="actions">
                        <form action="<?= e(url('admin/yorumlar/' . $r['id'] . '/onayla')) ?>" method="post"><?= csrf_field() ?><button class="btn <?= (int) $r['is_approved'] ? 'btn-outline' : 'btn-dark' ?> btn-sm"><?= (int) $r['is_approved'] ? 'Gizle' : 'Onayla' ?></button></form>
                        <form action="<?= e(url('admin/yorumlar/' . $r['id'] . '/sil')) ?>" method="post" data-confirm="Yorumu silmek istiyor musunuz?"><?= csrf_field() ?><button class="btn btn-ghost btn-icon btn-sm text-danger" title="Sil"><?= icon('trash', '', 16) ?></button></form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$result['items']): ?><tr class="empty-row"><td colspan="6">Yorum bulunamadı.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/partials-pagination', ['result' => $result]) ?>
</div>

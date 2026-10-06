<?= partial('admin/partials-head', ['title' => 'İletişim Mesajları', 'sub' => 'İletişim formundan gelen mesajlar · ' . $subscribers . ' bülten abonesi']) ?>
<div class="panel">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Gönderen</th><th>Konu</th><th>Mesaj</th><th>Tarih</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($result['items'] as $m): ?>
                <tr style="<?= (int) $m['is_read'] ? '' : 'background:var(--primary-50)' ?>">
                    <td><strong style="color:var(--ink)"><?= e($m['name']) ?></strong><div class="small text-muted"><?= e($m['email']) ?></div></td>
                    <td><?= (int) $m['is_read'] ? '' : badge('Yeni', 'primary') . ' ' ?><?= e($m['subject']) ?></td>
                    <td class="small"><?= e(str_limit($m['message'], 90)) ?></td>
                    <td class="small"><?= e(time_ago($m['created_at'])) ?></td>
                    <td><div class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('admin/mesajlar/' . $m['id'])) ?>">Oku</a></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$result['items']): ?><tr class="empty-row"><td colspan="5">Henüz mesaj yok.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/partials-pagination', ['result' => $result]) ?>
</div>

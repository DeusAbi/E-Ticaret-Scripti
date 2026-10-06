<?php use App\Services\Coupon; ?>
<?= partial('admin/partials-head', [
    'title' => 'Kuponlar', 'sub' => 'İndirim kuponları oluşturun ve kullanımlarını takip edin.',
    'actions' => '<a href="' . e(url('admin/kuponlar/yeni')) . '" class="btn btn-primary">' . icon('plus', '', 17) . ' Yeni Kupon</a>',
]) ?>
<div class="panel">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kod</th><th>İndirim</th><th>Min. Sepet</th><th>Kullanım</th><th>Geçerlilik</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($coupons as $c):
                $expired = $c['expires_at'] && $c['expires_at'] < now();
                $full = (int) $c['max_uses'] > 0 && (int) $c['used_count'] >= (int) $c['max_uses']; ?>
                <tr>
                    <td><code style="font-weight:700;color:var(--ink);background:var(--line-2);padding:4px 8px;border-radius:6px"><?= e($c['code']) ?></code></td>
                    <td class="fw-600"><?= e(Coupon::describe($c)) ?></td>
                    <td class="num"><?= (float) $c['min_total'] > 0 ? e(money($c['min_total'])) : '—' ?></td>
                    <td class="num"><?= (int) $c['used_count'] ?> / <?= (int) $c['max_uses'] ?: '∞' ?></td>
                    <td class="small"><?= $c['starts_at'] ? e(date('d.m.Y', strtotime($c['starts_at']))) : 'Hemen' ?> → <?= $c['expires_at'] ? e(date('d.m.Y', strtotime($c['expires_at']))) : 'Süresiz' ?></td>
                    <td><?= !(int) $c['is_active'] ? badge('Pasif', 'muted') : ($expired ? badge('Süresi doldu', 'danger') : ($full ? badge('Limit doldu', 'warning') : badge('Aktif', 'success'))) ?></td>
                    <td><div class="actions">
                        <a class="btn btn-outline btn-icon btn-sm" href="<?= e(url('admin/kuponlar/' . $c['id'] . '/duzenle')) ?>" title="Düzenle"><?= icon('edit', '', 16) ?></a>
                        <form action="<?= e(url('admin/kuponlar/' . $c['id'] . '/sil')) ?>" method="post" data-confirm="Kuponu silmek istiyor musunuz?"><?= csrf_field() ?><button class="btn btn-ghost btn-icon btn-sm text-danger" title="Sil"><?= icon('trash', '', 16) ?></button></form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$coupons): ?><tr class="empty-row"><td colspan="7">Henüz kupon oluşturulmamış.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

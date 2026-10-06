<?php $role = (string) input('rol', ''); ?>
<?= partial('admin/partials-head', ['title' => 'Müşteriler', 'sub' => 'Kayıtlı üyeleri görüntüleyin ve yönetin.']) ?>
<div class="seg">
    <a href="<?= e(url('admin/musteriler')) ?>" class="<?= $role === '' ? 'is-active' : '' ?>">Tümü</a>
    <a href="<?= e(url('admin/musteriler?rol=customer')) ?>" class="<?= $role === 'customer' ? 'is-active' : '' ?>">Müşteriler</a>
    <a href="<?= e(url('admin/musteriler?rol=admin')) ?>" class="<?= $role === 'admin' ? 'is-active' : '' ?>">Yöneticiler</a>
</div>
<div class="panel">
    <form class="filterbar" method="get">
        <?php if ($role): ?><input type="hidden" name="rol" value="<?= e($role) ?>"><?php endif; ?>
        <input class="form-control grow" type="search" name="q" value="<?= e(input('q', '')) ?>" placeholder="Ad, e-posta veya telefon ara…">
        <button class="btn btn-dark" type="submit"><?= icon('search', '', 16) ?> Ara</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Müşteri</th><th>Telefon</th><th>Sipariş</th><th>Harcama</th><th>Kayıt</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($result['items'] as $u): ?>
                <tr>
                    <td><div class="prod" style="min-width:220px"><span class="avatar"><?= e(initials($u['name'])) ?></span><div><a href="<?= e(url('admin/musteriler/' . $u['id'])) ?>"><?= e($u['name']) ?></a><small><?= e($u['email']) ?></small></div></div></td>
                    <td><?= e($u['phone'] ?: '—') ?></td>
                    <td><?= (int) $u['order_count'] ?></td>
                    <td class="num fw-600"><?= e(money($u['spent'])) ?></td>
                    <td class="num"><?= e(date('d.m.Y', strtotime($u['created_at']))) ?></td>
                    <td><?= $u['role'] === 'admin' ? badge('Yönetici', 'dark') : '' ?> <?= (int) $u['is_active'] ? badge('Aktif', 'success') : badge('Askıda', 'danger') ?></td>
                    <td><div class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('admin/musteriler/' . $u['id'])) ?>">Görüntüle</a></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$result['items']): ?><tr class="empty-row"><td colspan="7">Kayıt bulunamadı.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/partials-pagination', ['result' => $result]) ?>
</div>

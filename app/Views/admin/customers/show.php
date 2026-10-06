<?php /** @var array $user */ $self = (int) $user['id'] === (int) auth()['id']; ?>
<?= partial('admin/partials-head', ['title' => $user['name'], 'sub' => $user['email'], 'crumbs' => [['Müşteriler', 'admin/musteriler'], [$user['name'], null]]]) ?>
<div class="kpis">
    <div class="kpi"><div class="top"><span class="label">Toplam Sipariş</span><span class="ic"><?= icon('package', '', 20) ?></span></div><div class="value"><?= count($orders) ?></div></div>
    <div class="kpi"><div class="top"><span class="label">Toplam Harcama</span><span class="ic"><?= icon('wallet', '', 20) ?></span></div><div class="value"><?= e(money($spent)) ?></div></div>
    <div class="kpi"><div class="top"><span class="label">Ortalama Sepet</span><span class="ic"><?= icon('bag', '', 20) ?></span></div><div class="value"><?= e(money(count($orders) ? $spent / count($orders) : 0)) ?></div></div>
    <div class="kpi"><div class="top"><span class="label">Üyelik</span><span class="ic"><?= icon('clock', '', 20) ?></span></div><div class="value" style="font-size:18px"><?= e(date_tr($user['created_at'], false)) ?></div><div class="sub">Son giriş: <?= e($user['last_login_at'] ? time_ago($user['last_login_at']) : '—') ?></div></div>
</div>
<div class="order-grid">
    <div class="panel">
        <div class="panel-head"><h3><?= icon('package', '', 18) ?> Siparişler</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Sipariş</th><th>Tarih</th><th>Durum</th><th class="text-right">Tutar</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr><td><a class="link-strong" href="<?= e(url('admin/siparisler/' . $o['id'])) ?>">#<?= e($o['order_no']) ?></a></td><td><?= e(date_tr($o['created_at'])) ?></td><td><?= order_status_badge($o['status']) ?></td><td class="text-right num fw-600"><?= e(money($o['total'])) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?><tr class="empty-row"><td colspan="4">Bu müşterinin siparişi yok.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-side">
        <div class="panel">
            <div class="panel-head"><h3>Hesap</h3><?= (int) $user['is_active'] ? badge('Aktif', 'success') : badge('Askıda', 'danger') ?></div>
            <div class="panel-body kv">
                <div><span>E-posta</span><strong><?= e($user['email']) ?></strong></div>
                <div><span>Telefon</span><strong><?= e($user['phone'] ?: '—') ?></strong></div>
                <div><span>Rol</span><strong><?= $user['role'] === 'admin' ? 'Yönetici' : 'Müşteri' ?></strong></div>
                <div><span>Bülten</span><strong><?= (int) $user['newsletter'] ? 'Abone' : 'Değil' ?></strong></div>
            </div>
            <?php if (!$self): ?>
                <div class="panel-foot">
                    <form action="<?= e(url('admin/musteriler/' . $user['id'] . '/rol')) ?>" method="post" data-confirm="Kullanıcının yetkisini değiştirmek istediğinize emin misiniz?"><?= csrf_field() ?><button class="btn btn-ghost btn-sm"><?= $user['role'] === 'admin' ? 'Yöneticilikten çıkar' : 'Yönetici yap' ?></button></form>
                    <form action="<?= e(url('admin/musteriler/' . $user['id'] . '/durum')) ?>" method="post"><?= csrf_field() ?><button class="btn <?= (int) $user['is_active'] ? 'btn-danger' : 'btn-dark' ?> btn-sm"><?= (int) $user['is_active'] ? 'Hesabı Askıya Al' : 'Hesabı Aktifleştir' ?></button></form>
                </div>
            <?php endif; ?>
        </div>
        <div class="panel">
            <div class="panel-head"><h3><?= icon('pin', '', 18) ?> Adresler</h3></div>
            <div class="panel-body">
                <?php foreach ($addresses as $a): ?>
                    <p class="small"><strong style="color:var(--ink)"><?= e($a['title']) ?></strong> <?= (int) $a['is_default'] ? badge('Varsayılan', 'primary') : '' ?><br><?= e($a['full_name']) ?> · <?= e($a['phone']) ?><br><?= e($a['address']) ?>, <?= e($a['district']) ?> / <?= e($a['city']) ?></p>
                <?php endforeach; ?>
                <?php if (!$addresses): ?><p class="text-muted small mb-0">Kayıtlı adres yok.</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

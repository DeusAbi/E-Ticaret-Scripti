<?php ob_start(); $a = $edit ?? []; $val = static fn ($k, $d = '') => old($k, $a[$k] ?? $d); ?>
<div class="page-title mb-2"><h1>Adreslerim</h1><a href="#adres-formu" class="btn btn-dark btn-sm"><?= icon('plus', '', 16) ?> Yeni Adres</a></div>
<?php if ($addresses): ?>
    <div class="address-grid">
        <?php foreach ($addresses as $ad): ?>
            <div class="address-card<?= (int) $ad['is_default'] ? ' is-default' : '' ?>">
                <h4><?= icon('pin', '', 18) ?> <?= e($ad['title']) ?> <?php if ((int) $ad['is_default']): ?><span class="badge badge-primary">Varsayılan</span><?php endif; ?></h4>
                <p><strong style="color:var(--ink)"><?= e($ad['full_name']) ?></strong> · <?= e($ad['phone']) ?></p>
                <p><?= e($ad['address']) ?></p>
                <p><?= e($ad['district']) ?> / <?= e($ad['city']) ?> <?= e($ad['postal_code']) ?></p>
                <div class="actions">
                    <a href="<?= e(url('hesabim/adresler?duzenle=' . $ad['id'] . '#adres-formu')) ?>" class="btn btn-outline btn-sm"><?= icon('edit', '', 15) ?> Düzenle</a>
                    <?php if (!(int) $ad['is_default']): ?>
                        <form action="<?= e(url('hesabim/adresler/' . $ad['id'] . '/varsayilan')) ?>" method="post"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit">Varsayılan Yap</button></form>
                    <?php endif; ?>
                    <form action="<?= e(url('hesabim/adresler/' . $ad['id'] . '/sil')) ?>" method="post" data-confirm="Bu adresi silmek istediğinize emin misiniz?"><?= csrf_field() ?><button class="btn btn-ghost btn-sm text-danger" type="submit"><?= icon('trash', '', 15) ?> Sil</button></form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?= partial('partials/empty', ['icon' => 'pin', 'title' => 'Kayıtlı adresiniz yok', 'text' => 'Siparişlerinizi daha hızlı tamamlamak için adres ekleyin.']) ?>
<?php endif; ?>

<div class="card mt-3" id="adres-formu">
    <div class="card-head"><h3><?= $edit ? 'Adresi Düzenle' : 'Yeni Adres Ekle' ?></h3><?php if ($edit): ?><a href="<?= e(url('hesabim/adresler')) ?>" class="btn btn-ghost btn-sm">Vazgeç</a><?php endif; ?></div>
    <form class="card-body" action="<?= e(url('hesabim/adresler')) ?>" method="post">
        <?= csrf_field() ?>
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Adres Başlığı <span class="req">*</span></label><input class="form-control<?= has_error('title') ?>" name="title" value="<?= e($val('title')) ?>" placeholder="Ev, İş…"><?= field_error('title') ?></div>
            <div class="form-group"><label class="form-label">Ad Soyad <span class="req">*</span></label><input class="form-control<?= has_error('full_name') ?>" name="full_name" value="<?= e($val('full_name', auth()['name'])) ?>"><?= field_error('full_name') ?></div>
        </div>
        <div class="form-row-3">
            <div class="form-group"><label class="form-label">Telefon <span class="req">*</span></label><input class="form-control<?= has_error('phone') ?>" name="phone" type="tel" value="<?= e($val('phone', auth()['phone'] ?? '')) ?>"><?= field_error('phone') ?></div>
            <div class="form-group"><label class="form-label">İl <span class="req">*</span></label>
                <select class="form-control<?= has_error('city') ?>" name="city"><option value="">Seçiniz</option><?php foreach (tr_cities() as $c): ?><option <?= $val('city') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><?= field_error('city') ?></div>
            <div class="form-group"><label class="form-label">İlçe <span class="req">*</span></label><input class="form-control<?= has_error('district') ?>" name="district" value="<?= e($val('district')) ?>"><?= field_error('district') ?></div>
        </div>
        <div class="form-group"><label class="form-label">Açık Adres <span class="req">*</span></label><textarea class="form-control<?= has_error('address') ?>" name="address" rows="2"><?= e($val('address')) ?></textarea><?= field_error('address') ?></div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Posta Kodu</label><input class="form-control" name="postal_code" value="<?= e($val('postal_code')) ?>"></div>
        </div>
        <button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> <?= $edit ? 'Adresi Güncelle' : 'Adresi Kaydet' ?></button>
    </form>
</div>
<?php $inner = ob_get_clean(); echo partial('account/layout', ['inner' => $inner, 'crumb' => 'Adreslerim']); ?>

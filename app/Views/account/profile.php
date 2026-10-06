<?php ob_start(); ?>
<div class="page-title mb-2"><h1>Profil & Şifre</h1></div>
<div class="card mb-2">
    <div class="card-head"><h3><?= icon('user', '', 19) ?> Kişisel Bilgiler</h3></div>
    <form class="card-body" action="<?= e(url('hesabim/profil')) ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Ad Soyad</label><input class="form-control<?= has_error('name') ?>" name="name" value="<?= e(old('name', $user['name'])) ?>" required><?= field_error('name') ?></div>
            <div class="form-group"><label class="form-label">Telefon</label><input class="form-control<?= has_error('phone') ?>" name="phone" type="tel" value="<?= e(old('phone', $user['phone'])) ?>"><?= field_error('phone') ?></div>
        </div>
        <div class="form-group"><label class="form-label">E-posta Adresi</label><input class="form-control<?= has_error('email') ?>" name="email" type="email" value="<?= e(old('email', $user['email'])) ?>" required><?= field_error('email') ?></div>
        <div class="form-group"><label class="check"><input type="checkbox" name="newsletter" value="1" <?= (int) $user['newsletter'] ? 'checked' : '' ?>> Kampanya ve fırsatlardan e-posta ile haberdar olmak istiyorum.</label></div>
        <button class="btn btn-primary" type="submit">Bilgilerimi Güncelle</button>
    </form>
</div>
<div class="card" id="sifre">
    <div class="card-head"><h3><?= icon('lock', '', 19) ?> Şifre Değiştir</h3></div>
    <form class="card-body" action="<?= e(url('hesabim/sifre')) ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label">Mevcut Şifre</label><input class="form-control<?= has_error('current_password') ?>" type="password" name="current_password" autocomplete="current-password" required><?= field_error('current_password') ?></div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Yeni Şifre</label><input class="form-control<?= has_error('password') ?>" type="password" name="password" autocomplete="new-password" required minlength="8" data-pw-meter><div class="pw-meter" data-pw-bar><i></i><i></i><i></i><i></i></div><?= field_error('password') ?></div>
            <div class="form-group"><label class="form-label">Yeni Şifre Tekrarı</label><input class="form-control<?= has_error('password_confirmation') ?>" type="password" name="password_confirmation" autocomplete="new-password" required><?= field_error('password_confirmation') ?></div>
        </div>
        <button class="btn btn-dark" type="submit">Şifremi Değiştir</button>
    </form>
</div>
<p class="small text-muted mt-2">Üyelik tarihi: <?= e(date_tr($user['created_at'], false)) ?></p>
<?php $inner = ob_get_clean(); echo partial('account/layout', ['inner' => $inner, 'crumb' => 'Profil & Şifre']); ?>

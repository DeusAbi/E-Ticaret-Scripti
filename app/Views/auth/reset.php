<div class="container">
    <div class="auth-wrap">
        <?= partial('auth/aside', ['heading' => 'Yeni şifrenizi belirleyin', 'sub' => 'Güçlü bir şifre seçerek hesabınızı koruma altına alın.']) ?>
        <div class="auth-main">
            <div class="inner">
                <h1>Yeni Şifre Belirle</h1>
                <p class="lead"><?= e(mask_email($email)) ?> hesabı için yeni şifrenizi girin.</p>
                <form action="<?= e(url('sifre-sifirla/' . $token)) ?>" method="post" data-submit-once>
                    <?= csrf_field() ?>
                    <input type="hidden" name="email" value="<?= e($email) ?>">
                    <div class="form-group">
                        <label class="form-label" for="password">Yeni Şifre</label>
                        <div class="input-icon"><?= icon('lock', '', 18) ?><input class="form-control<?= has_error('password') ?>" type="password" id="password" name="password" required minlength="8" autocomplete="new-password" data-pw-meter><button type="button" class="toggle-pass" data-toggle-pass aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button></div>
                        <div class="pw-meter" data-pw-bar><i></i><i></i><i></i><i></i></div>
                        <?= field_error('password') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Yeni Şifre Tekrarı</label>
                        <div class="input-icon"><?= icon('lock', '', 18) ?><input class="form-control<?= has_error('password_confirmation') ?>" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"></div>
                        <?= field_error('password_confirmation') ?>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg" type="submit">Şifremi Güncelle</button>
                </form>
            </div>
        </div>
    </div>
</div>

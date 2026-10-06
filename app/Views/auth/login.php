<div class="container">
    <div class="auth-wrap">
        <?= partial('auth/aside', ['heading' => 'Tekrar hoş geldiniz!', 'sub' => 'Hesabınıza giriş yaparak siparişlerinizi takip edin, favorilerinize ulaşın.']) ?>
        <div class="auth-main">
            <div class="inner">
                <div class="auth-tabs"><a href="<?= e(url('giris' . ($redirect ? '?redirect=' . rawurlencode($redirect) : ''))) ?>" class="is-active">Giriş Yap</a><a href="<?= e(url('kayit')) ?>">Üye Ol</a></div>
                <h1>Giriş Yap</h1>
                <p class="lead">E-posta adresiniz ve şifrenizle giriş yapın.</p>
                <form action="<?= e(url('giris')) ?>" method="post" data-submit-once>
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                    <div class="form-group">
                        <label class="form-label" for="email">E-posta Adresi</label>
                        <div class="input-icon"><?= icon('mail', '', 18) ?><input class="form-control<?= has_error('email') ?>" type="email" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="ornek@mail.com" autocomplete="email" required autofocus></div>
                        <?= field_error('email') ?>
                    </div>
                    <div class="form-group">
                        <div class="flex justify-between items-center"><label class="form-label" for="password">Şifre</label><a class="link small" href="<?= e(url('sifremi-unuttum')) ?>">Şifremi unuttum</a></div>
                        <div class="input-icon"><?= icon('lock', '', 18) ?><input class="form-control<?= has_error('password') ?>" type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required><button type="button" class="toggle-pass" data-toggle-pass aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button></div>
                        <?= field_error('password') ?>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg" type="submit">Giriş Yap <?= icon('arrow-right', '', 18) ?></button>
                </form>
                <p class="auth-switch">Hesabınız yok mu? <a class="link" href="<?= e(url('kayit')) ?>">Hemen üye olun</a></p>
                <?php if (config('app.debug')): ?>
                    <div class="demo-box"><strong>Demo hesaplar</strong><br>Yönetici: <code>admin@example.com</code> / <code>admin123</code><br>Müşteri: <code>demo@example.com</code> / <code>demo123</code></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

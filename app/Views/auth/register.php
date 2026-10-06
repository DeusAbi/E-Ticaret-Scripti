<div class="container">
    <div class="auth-wrap">
        <?= partial('auth/aside', ['heading' => 'Aramıza katılın, kazanmaya başlayın.', 'sub' => 'Ücretsiz üyelik ile size özel kampanyalardan yararlanın, siparişlerinizi kolayca takip edin.']) ?>
        <div class="auth-main">
            <div class="inner">
                <div class="auth-tabs"><a href="<?= e(url('giris')) ?>">Giriş Yap</a><a href="<?= e(url('kayit')) ?>" class="is-active">Üye Ol</a></div>
                <h1>Hesap Oluştur</h1>
                <p class="lead">Birkaç saniyede ücretsiz üye olun.</p>
                <form action="<?= e(url('kayit')) ?>" method="post" data-submit-once>
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="name">Ad Soyad <span class="req">*</span></label>
                        <div class="input-icon"><?= icon('user', '', 18) ?><input class="form-control<?= has_error('name') ?>" id="name" name="name" value="<?= e(old('name')) ?>" placeholder="Adınız ve soyadınız" autocomplete="name" required></div>
                        <?= field_error('name') ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="email">E-posta <span class="req">*</span></label>
                            <input class="form-control<?= has_error('email') ?>" type="email" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="ornek@mail.com" autocomplete="email" required>
                            <?= field_error('email') ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Telefon</label>
                            <input class="form-control<?= has_error('phone') ?>" type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>" placeholder="05XX XXX XX XX" autocomplete="tel">
                            <?= field_error('phone') ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Şifre <span class="req">*</span></label>
                        <div class="input-icon"><?= icon('lock', '', 18) ?><input class="form-control<?= has_error('password') ?>" type="password" id="password" name="password" placeholder="En az 8 karakter, harf ve rakam" autocomplete="new-password" required minlength="8" data-pw-meter><button type="button" class="toggle-pass" data-toggle-pass aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button></div>
                        <div class="pw-meter" data-pw-bar><i></i><i></i><i></i><i></i></div>
                        <?= field_error('password') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Şifre Tekrarı <span class="req">*</span></label>
                        <div class="input-icon"><?= icon('lock', '', 18) ?><input class="form-control<?= has_error('password_confirmation') ?>" type="password" id="password_confirmation" name="password_confirmation" placeholder="Şifrenizi tekrar girin" autocomplete="new-password" required></div>
                        <?= field_error('password_confirmation') ?>
                    </div>
                    <div class="form-group">
                        <label class="check"><input type="checkbox" name="terms" value="1" <?= old('terms') ? 'checked' : '' ?> required> <span><a class="link" href="<?= e(url('sayfa/kvkk-aydinlatma-metni')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni ve <a class="link" href="<?= e(url('sayfa/gizlilik-ve-cerez-politikasi')) ?>" target="_blank">Gizlilik Politikası</a>'nı okudum, kabul ediyorum.</span></label>
                        <?= field_error('terms') ?>
                    </div>
                    <div class="form-group">
                        <label class="check"><input type="checkbox" name="newsletter" value="1" <?= old('newsletter') ? 'checked' : '' ?>> <span>Kampanya ve fırsatlardan e-posta ile haberdar olmak istiyorum.</span></label>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg" type="submit">Üye Ol <?= icon('arrow-right', '', 18) ?></button>
                </form>
                <p class="auth-switch">Zaten üye misiniz? <a class="link" href="<?= e(url('giris')) ?>">Giriş yapın</a></p>
            </div>
        </div>
    </div>
</div>

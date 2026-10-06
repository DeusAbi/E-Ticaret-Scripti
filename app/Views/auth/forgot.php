<div class="container">
    <div class="auth-wrap">
        <?= partial('auth/aside', ['heading' => 'Şifrenizi mi unuttunuz?', 'sub' => 'Endişelenmeyin, birkaç adımda yeni bir şifre belirleyebilirsiniz.']) ?>
        <div class="auth-main">
            <div class="inner">
                <h1>Şifremi Unuttum</h1>
                <p class="lead">Hesabınıza kayıtlı e-posta adresini girin, size şifre sıfırlama bağlantısı gönderelim.</p>
                <form action="<?= e(url('sifremi-unuttum')) ?>" method="post" data-submit-once>
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="email">E-posta Adresi</label>
                        <div class="input-icon"><?= icon('mail', '', 18) ?><input class="form-control<?= has_error('email') ?>" type="email" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="ornek@mail.com" required autofocus></div>
                        <?= field_error('email') ?>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg" type="submit"><?= icon('send', '', 18) ?> Sıfırlama Bağlantısı Gönder</button>
                </form>
                <p class="auth-switch"><a class="link" href="<?= e(url('giris')) ?>">← Giriş sayfasına dön</a></p>
            </div>
        </div>
    </div>
</div>

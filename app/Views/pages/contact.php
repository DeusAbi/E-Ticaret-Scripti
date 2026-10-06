<div class="container">
    <div class="page-head">
        <?= partial('partials/breadcrumbs', ['items' => [['İletişim', null]]]) ?>
        <div class="page-title"><div><h1>Bize Ulaşın</h1><p>Sorularınız, önerileriniz veya talepleriniz için bize her zaman yazabilirsiniz.</p></div></div>
    </div>
    <div class="contact-grid">
        <div class="contact-info">
            <div class="card contact-item"><span class="ic"><?= icon('phone', '', 22) ?></span><div><strong>Müşteri Hizmetleri</strong><a href="tel:<?= e(preg_replace('/\s+/', '', (string) setting('store_phone'))) ?>"><?= e(setting('store_phone')) ?></a><br><span>Hafta içi 09:00 – 18:00</span></div></div>
            <div class="card contact-item"><span class="ic"><?= icon('mail', '', 22) ?></span><div><strong>E-posta</strong><a href="mailto:<?= e(setting('store_email')) ?>"><?= e(setting('store_email')) ?></a><br><span>24 saat içinde yanıt</span></div></div>
            <?php if (setting('store_whatsapp')): ?>
                <div class="card contact-item"><span class="ic"><?= icon('message', '', 22) ?></span><div><strong>WhatsApp Destek</strong><a href="https://wa.me/<?= e(preg_replace('/\D+/', '', (string) setting('store_whatsapp'))) ?>" target="_blank" rel="noopener">Mesaj gönderin</a></div></div>
            <?php endif; ?>
            <div class="card contact-item"><span class="ic"><?= icon('pin', '', 22) ?></span><div><strong>Adres</strong><span><?= e(setting('store_address')) ?></span></div></div>
        </div>
        <div class="card">
            <div class="card-head"><h3><?= icon('send', '', 19) ?> Mesaj Gönderin</h3></div>
            <form class="card-body" action="<?= e(url('iletisim')) ?>" method="post" data-submit-once>
                <?= csrf_field() ?>
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Ad Soyad <span class="req">*</span></label><input class="form-control<?= has_error('name') ?>" name="name" value="<?= e(old('name', auth()['name'] ?? '')) ?>" required><?= field_error('name') ?></div>
                    <div class="form-group"><label class="form-label">E-posta <span class="req">*</span></label><input class="form-control<?= has_error('email') ?>" type="email" name="email" value="<?= e(old('email', auth()['email'] ?? '')) ?>" required><?= field_error('email') ?></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Telefon</label><input class="form-control<?= has_error('phone') ?>" type="tel" name="phone" value="<?= e(old('phone')) ?>"><?= field_error('phone') ?></div>
                    <div class="form-group"><label class="form-label">Konu <span class="req">*</span></label>
                        <select class="form-control<?= has_error('subject') ?>" name="subject" required>
                            <?php foreach (['Sipariş hakkında', 'İade ve değişim', 'Ürün bilgisi', 'Ödeme sorunları', 'Öneri / Şikayet', 'Diğer'] as $s): ?><option <?= old('subject') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                        </select><?= field_error('subject') ?></div>
                </div>
                <div class="form-group"><label class="form-label">Mesajınız <span class="req">*</span></label><textarea class="form-control<?= has_error('message') ?>" name="message" rows="6" required minlength="10"><?= e(old('message')) ?></textarea><?= field_error('message') ?></div>
                <button class="btn btn-primary btn-lg" type="submit"><?= icon('send', '', 18) ?> Mesajı Gönder</button>
            </form>
        </div>
    </div>
</div>

<?php $s = $settings; $v = static fn ($k) => old($k, $s[$k] ?? ''); $chk = static fn ($k) => ($s[$k] ?? '0') === '1' ? 'checked' : ''; ?>
<?= partial('admin/partials-head', ['title' => 'Mağaza Ayarları', 'sub' => 'Mağaza bilgileri, görünüm, kargo ve ödeme seçenekleri.']) ?>
<form action="<?= e(url('admin/ayarlar')) ?>" method="post" enctype="multipart/form-data" data-settings>
    <?= csrf_field() ?>
    <input type="hidden" name="tab" value="genel" data-tab-input>
    <nav class="tabs-admin" data-admin-tabs>
        <a href="#genel" class="is-active"><?= icon('store', '', 17) ?> Genel</a>
        <a href="#gorunum"><?= icon('image', '', 17) ?> Görünüm</a>
        <a href="#kargo"><?= icon('truck', '', 17) ?> Kargo</a>
        <a href="#odeme"><?= icon('card', '', 17) ?> Ödeme</a>
        <a href="#sosyal"><?= icon('instagram', '', 17) ?> Sosyal Medya & SEO</a>
    </nav>

    <div class="tab-pane is-active" id="genel">
        <div class="panel">
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Mağaza Adı <span class="req">*</span></label><input class="form-control<?= has_error('store_name') ?>" name="store_name" value="<?= e($v('store_name')) ?>" required><?= field_error('store_name') ?></div>
                    <div class="form-group"><label class="form-label">Slogan</label><input class="form-control" name="store_tagline" value="<?= e($v('store_tagline')) ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">E-posta <span class="req">*</span></label><input class="form-control<?= has_error('store_email') ?>" type="email" name="store_email" value="<?= e($v('store_email')) ?>" required><div class="form-hint">Yeni sipariş ve mesaj bildirimleri bu adrese gönderilir.</div><?= field_error('store_email') ?></div>
                    <div class="form-group"><label class="form-label">Telefon</label><input class="form-control" name="store_phone" value="<?= e($v('store_phone')) ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">WhatsApp Numarası</label><input class="form-control" name="store_whatsapp" value="<?= e($v('store_whatsapp')) ?>" placeholder="905xxxxxxxxx"></div>
                    <div class="form-group"><label class="form-label">Şirket Ünvanı</label><input class="form-control" name="store_company" value="<?= e($v('store_company')) ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Vergi Dairesi / No</label><input class="form-control" name="store_tax_info" value="<?= e($v('store_tax_info')) ?>"></div>
                    <div class="form-group"><label class="form-label">MERSİS No</label><input class="form-control" name="store_mersis" value="<?= e($v('store_mersis')) ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Adres</label><textarea class="form-control" name="store_address" rows="2"><?= e($v('store_address')) ?></textarea></div>
                <div class="form-group mb-0"><label class="form-label">Footer Hakkımızda Metni</label><textarea class="form-control" name="footer_about" rows="3"><?= e($v('footer_about')) ?></textarea></div>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="gorunum">
        <div class="panel">
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ana Renk</label>
                        <div class="flex gap-1 items-center">
                            <input type="color" name="primary_color" value="<?= e($v('primary_color')) ?>" style="width:60px;height:48px;border:1px solid var(--line);border-radius:12px;padding:4px;background:#fff" data-color-input>
                            <div class="flex gap-1 wrap" data-color-presets>
                                <?php foreach (['#ef5a24', '#e11d48', '#7c3aed', '#2563eb', '#0891b2', '#059669', '#ca8a04', '#111827'] as $c): ?>
                                    <button type="button" data-color="<?= $c ?>" style="width:30px;height:30px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px var(--line);background:<?= $c ?>" aria-label="<?= $c ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?= field_error('primary_color') ?>
                        <div class="form-hint">Butonlar, rozetler ve vurgular bu rengi kullanır.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Logo</label>
                        <?php if (!empty($s['store_logo'])): ?>
                            <div class="flex items-center gap-2 mb-1"><img src="<?= e(media($s['store_logo'])) ?>" alt="" style="max-height:44px"><label class="check"><input type="checkbox" name="remove_logo" value="1"> Kaldır</label></div>
                        <?php endif; ?>
                        <input class="form-control" type="file" name="logo" accept="image/*" style="padding-top:11px">
                        <div class="form-hint">Boş bırakılırsa mağaza adı ile otomatik logo kullanılır.</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group mb-0"><label class="form-label">Üst Bar Duyuru Metni</label><input class="form-control" name="top_bar_text" value="<?= e($v('top_bar_text')) ?>"></div>
                    <div class="form-group mb-0"><label class="form-label">Sayfa Başına Ürün</label><input class="form-control<?= has_error('products_per_page') ?>" type="number" min="4" max="60" name="products_per_page" value="<?= e($v('products_per_page')) ?>"><?= field_error('products_per_page') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="kargo">
        <div class="panel">
            <div class="panel-body">
                <div class="form-row-3">
                    <div class="form-group"><label class="form-label">Kargo Ücreti</label><div class="input-addon"><input class="form-control<?= has_error('shipping_fee') ?>" name="shipping_fee" inputmode="decimal" value="<?= e(number_format((float) $v('shipping_fee'), 2, ',', '')) ?>"><span>₺</span></div><?= field_error('shipping_fee') ?></div>
                    <div class="form-group"><label class="form-label">Ücretsiz Kargo Limiti</label><div class="input-addon"><input class="form-control" name="free_shipping_threshold" inputmode="decimal" value="<?= e(number_format((float) $v('free_shipping_threshold'), 2, ',', '')) ?>"><span>₺</span></div><div class="form-hint">0 = ücretsiz kargo kapalı</div></div>
                    <div class="form-group"><label class="form-label">Kargoya Veriliş Süresi</label><input class="form-control" name="shipping_days" value="<?= e($v('shipping_days')) ?>"></div>
                </div>
                <div class="form-group mb-0"><label class="form-label">Kargo Firmaları</label><input class="form-control" name="cargo_companies" value="<?= e($v('cargo_companies')) ?>"><div class="form-hint">Virgülle ayırın. Sipariş detayında seçenek olarak listelenir.</div></div>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="odeme">
        <div class="panel">
            <div class="panel-head"><h3>Ödeme Yöntemleri</h3></div>
            <div class="panel-body" style="display:grid;gap:18px">
                <label class="switch"><input type="checkbox" name="card_enabled" value="1" <?= $chk('card_enabled') ?>><span class="track"></span><span>Kredi / Banka Kartı<small>Sanal POS: <?= e(App\Payment\Gateway::make()->name()) ?> (config/config.php → payment.gateway)</small></span></label>
                <label class="switch"><input type="checkbox" name="transfer_enabled" value="1" <?= $chk('transfer_enabled') ?>><span class="track"></span><span>Havale / EFT<small>Ödeme onayını sipariş detayından manuel verirsiniz</small></span></label>
                <label class="switch"><input type="checkbox" name="cod_enabled" value="1" <?= $chk('cod_enabled') ?>><span class="track"></span><span>Kapıda Ödeme</span></label>
                <div class="form-group mb-0" style="max-width:260px"><label class="form-label">Kapıda Ödeme Hizmet Bedeli</label><div class="input-addon"><input class="form-control" name="cod_fee" inputmode="decimal" value="<?= e(number_format((float) $v('cod_fee'), 2, ',', '')) ?>"><span>₺</span></div></div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Banka Hesapları (Havale/EFT)</h3></div>
            <div class="panel-body">
                <textarea class="form-control" name="bank_accounts" rows="4" style="font-family:ui-monospace,Menlo,monospace;font-size:13.5px"><?= e($v('bank_accounts')) ?></textarea>
                <div class="form-hint">Her satıra bir hesap: <code>Banka Adı | Alıcı Adı | IBAN</code></div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h3>Yorumlar</h3></div>
            <div class="panel-body"><label class="switch"><input type="checkbox" name="review_auto_approve" value="1" <?= $chk('review_auto_approve') ?>><span class="track"></span><span>Yorumları otomatik onayla<small>Kapalıyken yorumlar yönetici onayından sonra yayınlanır</small></span></label></div>
        </div>
    </div>

    <div class="tab-pane" id="sosyal">
        <div class="panel">
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Instagram</label><input class="form-control" name="social_instagram" value="<?= e($v('social_instagram')) ?>" placeholder="https://instagram.com/…"></div>
                    <div class="form-group"><label class="form-label">Facebook</label><input class="form-control" name="social_facebook" value="<?= e($v('social_facebook')) ?>"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">X (Twitter)</label><input class="form-control" name="social_twitter" value="<?= e($v('social_twitter')) ?>"></div>
                    <div class="form-group"><label class="form-label">YouTube</label><input class="form-control" name="social_youtube" value="<?= e($v('social_youtube')) ?>"></div>
                </div>
                <div class="form-group mb-0"><label class="form-label">Varsayılan SEO Açıklaması</label><textarea class="form-control" name="meta_description" rows="2" maxlength="300"><?= e($v('meta_description')) ?></textarea></div>
            </div>
        </div>
    </div>

    <div class="flex mt-3" style="justify-content:flex-end"><button class="btn btn-primary btn-lg" type="submit"><?= icon('check', '', 18) ?> Ayarları Kaydet</button></div>
</form>

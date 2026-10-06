<?php
/** @var array $summary @var array $addresses @var array $methods */
$oldAddress = old('address_id', $addresses[0]['id'] ?? 'new');
$oldMethod = old('payment_method', array_key_first($methods));
$oldInvoice = old('invoice_type', 'individual');
$user = auth();
?>
<div class="container">
    <?= partial('partials/checkout-steps', ['current' => 2]) ?>

    <form action="<?= e(url('odeme')) ?>" method="post" class="checkout-layout" data-checkout novalidate>
        <?= csrf_field() ?>
        <div class="checkout-main">
            <!-- Adres -->
            <section class="card">
                <div class="card-head"><h3><span class="num">1</span> Teslimat Adresi</h3></div>
                <div class="card-body">
                    <div class="option-grid">
                        <?php foreach ($addresses as $a): ?>
                            <label class="option-card">
                                <input type="radio" name="address_id" value="<?= (int) $a['id'] ?>" <?= (string) $oldAddress === (string) $a['id'] ? 'checked' : '' ?> data-address-option>
                                <span class="box">
                                    <strong><?= e($a['title']) ?> <?php if ((int) $a['is_default']): ?><span class="badge badge-primary">Varsayılan</span><?php endif; ?></strong>
                                    <span class="muted"><?= e($a['full_name']) ?> · <?= e($a['phone']) ?><br><?= e($a['address']) ?><br><?= e($a['district']) ?> / <?= e($a['city']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                        <label class="option-card <?= $addresses ? 'add' : '' ?>">
                            <input type="radio" name="address_id" value="new" <?= $oldAddress === 'new' || !$addresses ? 'checked' : '' ?> data-address-option>
                            <span class="box"><?= $addresses ? icon('plus', '', 18) . ' Yeni adres ekle' : '<strong>Yeni adres</strong><span class="muted">Teslimat adresinizi girin</span>' ?></span>
                        </label>
                    </div>
                    <?= field_error('address_id') ?>

                    <div class="mt-3 <?= $oldAddress === 'new' || !$addresses ? '' : 'hidden' ?>" data-new-address>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Ad Soyad <span class="req">*</span></label>
                                <input class="form-control<?= has_error('full_name') ?>" name="full_name" value="<?= e(old('full_name', $user['name'])) ?>" autocomplete="name">
                                <?= field_error('full_name') ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefon <span class="req">*</span></label>
                                <input class="form-control<?= has_error('phone') ?>" name="phone" type="tel" value="<?= e(old('phone', $user['phone'] ?? '')) ?>" placeholder="05XX XXX XX XX" autocomplete="tel">
                                <?= field_error('phone') ?>
                            </div>
                        </div>
                        <div class="form-row-3">
                            <div class="form-group">
                                <label class="form-label">İl <span class="req">*</span></label>
                                <select class="form-control<?= has_error('city') ?>" name="city">
                                    <option value="">Seçiniz</option>
                                    <?php foreach (tr_cities() as $c): ?><option <?= old('city') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                                </select>
                                <?= field_error('city') ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">İlçe <span class="req">*</span></label>
                                <input class="form-control<?= has_error('district') ?>" name="district" value="<?= e(old('district')) ?>">
                                <?= field_error('district') ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Posta Kodu</label>
                                <input class="form-control" name="postal_code" value="<?= e(old('postal_code')) ?>" inputmode="numeric" autocomplete="postal-code">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Açık Adres <span class="req">*</span></label>
                            <textarea class="form-control<?= has_error('address') ?>" name="address" rows="2" placeholder="Mahalle, cadde, sokak, bina ve daire no" autocomplete="street-address"><?= e(old('address')) ?></textarea>
                            <?= field_error('address') ?>
                        </div>
                        <div class="flex items-center gap-2 wrap">
                            <label class="check"><input type="checkbox" name="save_address" value="1" checked> Bu adresi kaydet</label>
                            <input class="form-control" name="address_title" placeholder="Adres başlığı (Ev, İş…)" style="max-width:220px;height:40px" value="<?= e(old('address_title')) ?>">
                        </div>
                    </div>

                    <hr>
                    <label class="form-label">Fatura Tipi</label>
                    <div class="flex gap-2 wrap">
                        <label class="check"><input type="radio" name="invoice_type" value="individual" <?= $oldInvoice === 'individual' ? 'checked' : '' ?> data-invoice-type> Bireysel</label>
                        <label class="check"><input type="radio" name="invoice_type" value="corporate" <?= $oldInvoice === 'corporate' ? 'checked' : '' ?> data-invoice-type> Kurumsal</label>
                    </div>
                    <div class="invoice-fields <?= $oldInvoice === 'corporate' ? '' : 'hidden' ?>" data-corporate>
                        <div class="form-row-3">
                            <div class="form-group"><label class="form-label">Firma Adı</label><input class="form-control<?= has_error('invoice_company') ?>" name="invoice_company" value="<?= e(old('invoice_company')) ?>"><?= field_error('invoice_company') ?></div>
                            <div class="form-group"><label class="form-label">Vergi Dairesi</label><input class="form-control<?= has_error('invoice_tax_office') ?>" name="invoice_tax_office" value="<?= e(old('invoice_tax_office')) ?>"><?= field_error('invoice_tax_office') ?></div>
                            <div class="form-group"><label class="form-label">Vergi No</label><input class="form-control<?= has_error('invoice_tax_no') ?>" name="invoice_tax_no" value="<?= e(old('invoice_tax_no')) ?>" inputmode="numeric"><?= field_error('invoice_tax_no') ?></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Ödeme -->
            <section class="card">
                <div class="card-head"><h3><span class="num">2</span> Ödeme Yöntemi</h3><span class="small text-muted flex items-center gap-1"><?= icon('lock', '', 14) ?> Güvenli ödeme</span></div>
                <div class="card-body">
                    <div class="pay-methods option-grid">
                        <?php foreach ($methods as $key => $m): ?>
                            <label class="option-card">
                                <input type="radio" name="payment_method" value="<?= e($key) ?>" <?= $oldMethod === $key ? 'checked' : '' ?> data-pay-method>
                                <span class="box">
                                    <?= icon($m['icon'], '', 22) ?>
                                    <strong><?= e($m['label']) ?></strong>
                                    <span class="muted"><?= match ($key) { 'card' => 'Tek çekim, anında onay', 'transfer' => 'Banka hesabına ödeme', 'cod' => '+' . e(money($codFee)) . ' hizmet bedeli' } ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= field_error('payment_method') ?>

                    <?php if (isset($methods['card'])): ?>
                        <div class="pay-panel <?= $oldMethod === 'card' ? '' : 'hidden' ?>" data-pay-panel="card">
                            <?php if (error('card')): ?><div class="alert alert-error"><?= icon('alert', '', 18) ?> <?= e(error('card')) ?></div><?php endif; ?>
                            <div class="card-form">
                                <div>
                                    <div class="form-group">
                                        <label class="form-label">Kart Üzerindeki İsim</label>
                                        <input class="form-control" name="card[holder]" autocomplete="cc-name" data-cc="holder" placeholder="AD SOYAD">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Kart Numarası</label>
                                        <div class="input-icon"><?= icon('card', '', 18) ?><input class="form-control" name="card[number]" inputmode="numeric" autocomplete="cc-number" placeholder="0000 0000 0000 0000" maxlength="23" data-cc="number"></div>
                                    </div>
                                    <div class="form-row-3">
                                        <div class="form-group">
                                            <label class="form-label">Ay</label>
                                            <select class="form-control" name="card[exp_month]" autocomplete="cc-exp-month" data-cc="month">
                                                <?php for ($m = 1; $m <= 12; $m++): ?><option value="<?= $m ?>" <?= $m === (int) date('n') ? 'selected' : '' ?>><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></option><?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Yıl</label>
                                            <select class="form-control" name="card[exp_year]" autocomplete="cc-exp-year" data-cc="year">
                                                <?php for ($y = (int) date('Y'); $y <= (int) date('Y') + 12; $y++): ?><option value="<?= $y ?>"><?= $y ?></option><?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">CVC</label>
                                            <input class="form-control" name="card[cvc]" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="•••">
                                        </div>
                                    </div>
                                    <?php if (config('payment.gateway', 'demo') === 'demo'): ?>
                                        <div class="alert alert-info small mb-0"><?= icon('info', '', 16) ?> <span><strong>Demo ödeme modu:</strong> Gerçek tahsilat yapılmaz. Test için <code>4242 4242 4242 4242</code> kartını, ret senaryosu için <code>4000 0000 0000 0002</code> kartını kullanabilirsiniz.</span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="credit-card" aria-hidden="true">
                                    <span class="cc-brand" data-cc-brand>VISA</span>
                                    <span class="chip-ic"></span>
                                    <div class="cc-number" data-cc-out="number">•••• •••• •••• ••••</div>
                                    <div class="cc-row">
                                        <div><small>Kart Sahibi</small><span data-cc-out="holder">AD SOYAD</span></div>
                                        <div><small>Son Kul.</small><span data-cc-out="exp"><?= date('m/y') ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($methods['transfer'])): ?>
                        <div class="pay-panel <?= $oldMethod === 'transfer' ? '' : 'hidden' ?>" data-pay-panel="transfer">
                            <p class="small text-muted">Siparişinizi tamamladıktan sonra aşağıdaki hesaplardan birine ödeme yapabilirsiniz. Açıklama kısmına <strong>sipariş numaranızı</strong> yazmayı unutmayın. Ödemeniz onaylandığında siparişiniz hazırlanmaya başlar.</p>
                            <div class="bank-list">
                                <?php foreach (bank_accounts() as $b): ?>
                                    <div class="bank"><div><strong><?= e($b['bank']) ?></strong><span class="small text-muted"><?= e($b['holder']) ?></span><br><code><?= e($b['iban']) ?></code></div><button type="button" class="copy-btn" data-copy="<?= e(str_replace(' ', '', $b['iban'])) ?>"><?= icon('copy', '', 14) ?> Kopyala</button></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($methods['cod'])): ?>
                        <div class="pay-panel <?= $oldMethod === 'cod' ? '' : 'hidden' ?>" data-pay-panel="cod">
                            <div class="alert alert-warning mb-0"><?= icon('info', '', 18) ?> <span>Kapıda ödeme seçeneğinde siparişinize <strong><?= e(money($codFee)) ?></strong> hizmet bedeli eklenir. Ödemeyi teslimat sırasında nakit veya kredi kartı ile yapabilirsiniz.</span></div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card">
                <div class="card-head"><h3><span class="num">3</span> Sipariş Notu <span class="small text-muted" style="font-weight:500">(isteğe bağlı)</span></h3></div>
                <div class="card-body">
                    <textarea class="form-control" name="note" rows="2" maxlength="500" placeholder="Siparişinizle ilgili eklemek istedikleriniz…"><?= e(old('note')) ?></textarea>
                </div>
            </section>
        </div>

        <aside class="summary">
            <div class="card card-body">
                <h3 class="mb-2">Sipariş Özeti</h3>
                <ul class="order-lines">
                    <?php foreach ($summary['items'] as $item): ?>
                        <li class="order-line">
                            <span class="thumb"><img src="<?= e(media($item['product']['image'])) ?>" alt=""><span class="q"><?= (int) $item['qty'] ?></span></span>
                            <span class="name"><?= e($item['product']['name']) ?></span>
                            <span class="val"><?= e(money($item['total'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?= partial('cart/summary-rows', ['summary' => $summary]) ?>
                <div class="mt-3">
                    <label class="check<?= has_error('agreement') ?>">
                        <input type="checkbox" name="agreement" value="1" required>
                        <span><a class="link" href="<?= e(url('sayfa/mesafeli-satis-sozlesmesi')) ?>" target="_blank">Mesafeli Satış Sözleşmesi</a>'ni ve <a class="link" href="<?= e(url('sayfa/teslimat-ve-iade')) ?>" target="_blank">Ön Bilgilendirme Formu</a>'nu okudum, onaylıyorum.</span>
                    </label>
                    <?= field_error('agreement') ?>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2" data-place-order><?= icon('lock', '', 18) ?> Siparişi Tamamla</button>
                <div class="secure-note"><?= icon('shield', '', 16) ?> Ödeme bilgileriniz 256-bit SSL ile korunur</div>
            </div>
            <a href="<?= e(url('sepet')) ?>" class="btn btn-ghost btn-block mt-1"><?= icon('arrow-left', '', 17) ?> Sepete dön</a>
        </aside>
    </form>
</div>
<script>window.CHECKOUT = { codFee: <?= json_encode((float) $codFee) ?>, currency: <?= json_encode((string) setting('currency_symbol', '₺')) ?> };</script>

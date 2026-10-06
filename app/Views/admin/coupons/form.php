<?php $c = $coupon ?? []; $v = static fn ($k, $d = '') => old($k, $c[$k] ?? $d); $isEdit = (bool) $coupon;
$date = static fn ($val) => $val ? date('Y-m-d', strtotime((string) $val)) : ''; ?>
<?= partial('admin/partials-head', ['title' => $isEdit ? 'Kupon: ' . $c['code'] : 'Yeni Kupon', 'crumbs' => [['Kuponlar', 'admin/kuponlar'], [$isEdit ? 'Düzenle' : 'Yeni', null]]]) ?>
<form action="<?= e(url($isEdit ? 'admin/kuponlar/' . $c['id'] : 'admin/kuponlar')) ?>" method="post" class="form-layout">
    <?= csrf_field() ?>
    <div class="panel">
        <div class="panel-body">
            <div class="form-group">
                <label class="form-label">Kupon Kodu <span class="req">*</span></label>
                <div class="input-group"><input class="form-control<?= has_error('code') ?>" name="code" value="<?= e($v('code')) ?>" required style="text-transform:uppercase;font-weight:700;letter-spacing:.06em" data-coupon-code><button type="button" class="btn btn-outline" data-generate-code><?= icon('zap', '', 16) ?> Üret</button></div>
                <?= field_error('code') ?>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">İndirim Tipi</label>
                    <select class="form-control" name="type"><option value="percent" <?= $v('type', 'percent') === 'percent' ? 'selected' : '' ?>>Yüzde (%)</option><option value="fixed" <?= $v('type') === 'fixed' ? 'selected' : '' ?>>Sabit tutar (₺)</option></select></div>
                <div class="form-group"><label class="form-label">İndirim Değeri <span class="req">*</span></label><input class="form-control<?= has_error('value') ?>" name="value" inputmode="decimal" value="<?= e($v('value') !== '' ? rtrim(rtrim(number_format((float) $v('value'), 2, ',', ''), '0'), ',') : '') ?>" required><?= field_error('value') ?></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Minimum Sepet Tutarı</label><div class="input-addon"><input class="form-control" name="min_total" inputmode="decimal" value="<?= e($v('min_total') !== '' ? number_format((float) $v('min_total'), 2, ',', '') : '') ?>"><span>₺</span></div></div>
                <div class="form-group"><label class="form-label">Toplam Kullanım Limiti</label><input class="form-control" type="number" min="0" name="max_uses" value="<?= e($v('max_uses', 0)) ?>"><div class="form-hint">0 = sınırsız</div></div>
            </div>
            <div class="form-row">
                <div class="form-group mb-0"><label class="form-label">Başlangıç Tarihi</label><input class="form-control" type="date" name="starts_at" value="<?= e($date($v('starts_at'))) ?>"></div>
                <div class="form-group mb-0"><label class="form-label">Bitiş Tarihi</label><input class="form-control" type="date" name="expires_at" value="<?= e($date($v('expires_at'))) ?>"></div>
            </div>
        </div>
    </div>
    <div class="form-side">
        <div class="panel">
            <div class="panel-body">
                <label class="switch"><input type="checkbox" name="is_active" value="1" <?= (int) $v('is_active', 1) ? 'checked' : '' ?>><span class="track"></span><span>Aktif</span></label>
                <?php if ($isEdit): ?><p class="small text-muted mt-2 mb-0">Bu kupon <?= (int) $c['used_count'] ?> kez kullanıldı.</p><?php endif; ?>
            </div>
            <div class="panel-foot"><a href="<?= e(url('admin/kuponlar')) ?>" class="btn btn-ghost btn-sm">Vazgeç</a><button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> Kaydet</button></div>
        </div>
    </div>
</form>

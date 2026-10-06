<?php $s = $slider ?? []; $v = static fn ($k, $d = '') => old($k, $s[$k] ?? $d); $isEdit = (bool) $slider; ?>
<?= partial('admin/partials-head', ['title' => $isEdit ? 'Slaytı Düzenle' : 'Yeni Slayt', 'crumbs' => [['Slider', 'admin/slider'], [$isEdit ? 'Düzenle' : 'Yeni', null]]]) ?>
<form action="<?= e(url($isEdit ? 'admin/slider/' . $s['id'] : 'admin/slider')) ?>" method="post" enctype="multipart/form-data" class="form-layout">
    <?= csrf_field() ?>
    <div class="panel">
        <div class="panel-body">
            <div class="form-group"><label class="form-label">Üst Başlık (etiket)</label><input class="form-control" name="eyebrow" value="<?= e($v('eyebrow')) ?>" maxlength="60" placeholder="Örn. Yeni Sezon"></div>
            <div class="form-group"><label class="form-label">Başlık <span class="req">*</span></label><input class="form-control<?= has_error('title') ?>" name="title" value="<?= e($v('title')) ?>" required maxlength="120"><?= field_error('title') ?></div>
            <div class="form-group"><label class="form-label">Alt Metin</label><textarea class="form-control" name="subtitle" rows="2" maxlength="300"><?= e($v('subtitle')) ?></textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Buton Metni</label><input class="form-control" name="button_text" value="<?= e($v('button_text')) ?>" maxlength="40"></div>
                <div class="form-group"><label class="form-label">Buton Bağlantısı</label><input class="form-control" name="button_link" value="<?= e($v('button_link')) ?>" placeholder="kategori/elektronik veya https://…"></div>
            </div>
            <div class="form-row-3">
                <div class="form-group mb-0"><label class="form-label">Arka Plan Rengi</label><input class="form-control<?= has_error('bg_color') ?>" type="color" name="bg_color" value="<?= e($v('bg_color', '#14161f')) ?>" style="padding:4px;height:48px"><?= field_error('bg_color') ?></div>
                <div class="form-group mb-0"><label class="form-label">Yazı Rengi</label><select class="form-control" name="theme"><option value="dark" <?= $v('theme', 'dark') === 'dark' ? 'selected' : '' ?>>Açık yazı (koyu zemin)</option><option value="light" <?= $v('theme') === 'light' ? 'selected' : '' ?>>Koyu yazı (açık zemin)</option></select></div>
                <div class="form-group mb-0"><label class="form-label">Sıra</label><input class="form-control" type="number" name="sort_order" value="<?= e($v('sort_order', 0)) ?>"></div>
            </div>
        </div>
    </div>
    <div class="form-side">
        <div class="panel">
            <div class="panel-body">
                <label class="switch"><input type="checkbox" name="is_active" value="1" <?= (int) $v('is_active', 1) ? 'checked' : '' ?>><span class="track"></span><span>Yayında</span></label>
                <hr>
                <label class="form-label">Görsel</label>
                <?php if (!empty($s['image'])): ?><img src="<?= e(media($s['image'])) ?>" alt="" style="max-height:160px;margin:0 auto 10px;border-radius:12px;background:<?= e($s['bg_color']) ?>"><?php endif; ?>
                <input class="form-control" type="file" name="image" accept="image/*" style="padding-top:11px">
                <div class="form-hint">Şeffaf arka planlı PNG/WEBP önerilir (600×600 px).</div>
            </div>
            <div class="panel-foot"><a href="<?= e(url('admin/slider')) ?>" class="btn btn-ghost btn-sm">Vazgeç</a><button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> Kaydet</button></div>
        </div>
    </div>
</form>

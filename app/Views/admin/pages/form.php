<?php $p = $page ?? []; $v = static fn ($k, $d = '') => old($k, $p[$k] ?? $d); $isEdit = (bool) $page; ?>
<?= partial('admin/partials-head', ['title' => $isEdit ? $p['title'] : 'Yeni Sayfa', 'crumbs' => [['Sayfalar', 'admin/sayfalar'], [$isEdit ? 'Düzenle' : 'Yeni', null]]]) ?>
<form action="<?= e(url($isEdit ? 'admin/sayfalar/' . $p['id'] : 'admin/sayfalar')) ?>" method="post" class="form-layout">
    <?= csrf_field() ?>
    <div class="panel">
        <div class="panel-body">
            <div class="form-group"><label class="form-label">Başlık <span class="req">*</span></label><input class="form-control<?= has_error('title') ?>" name="title" value="<?= e($v('title')) ?>" required><?= field_error('title') ?></div>
            <div class="form-group"><label class="form-label">URL (slug)</label><div class="input-addon left"><span>/sayfa/</span><input class="form-control" name="slug" value="<?= e($v('slug')) ?>" placeholder="otomatik"></div></div>
            <div class="form-group mb-0"><label class="form-label">İçerik</label><textarea class="form-control" name="content" rows="18"><?= e($v('content')) ?></textarea>
                <div class="form-hint">Biçimlendirme: <code>## Başlık</code>, <code>- madde</code>, <code>**kalın**</code>, <code>*italik*</code>. Paragraflar arasında boş satır bırakın.</div></div>
        </div>
    </div>
    <div class="form-side">
        <div class="panel">
            <div class="panel-body">
                <label class="switch mb-2"><input type="checkbox" name="is_active" value="1" <?= (int) $v('is_active', 1) ? 'checked' : '' ?>><span class="track"></span><span>Yayında</span></label>
                <div class="mt-2"><label class="switch"><input type="checkbox" name="show_in_footer" value="1" <?= (int) $v('show_in_footer', 1) ? 'checked' : '' ?>><span class="track"></span><span>Footer'da göster</span></label></div>
                <div class="form-group mt-2 mb-0"><label class="form-label">Sıra</label><input class="form-control" type="number" name="sort_order" value="<?= e($v('sort_order', 0)) ?>"></div>
            </div>
            <div class="panel-foot"><a href="<?= e(url('admin/sayfalar')) ?>" class="btn btn-ghost btn-sm">Vazgeç</a><button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> Kaydet</button></div>
        </div>
    </div>
</form>

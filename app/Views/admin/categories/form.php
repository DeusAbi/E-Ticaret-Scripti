<?php $c = $category ?? []; $v = static fn ($k, $d = '') => old($k, $c[$k] ?? $d); $isEdit = (bool) $category; ?>
<?= partial('admin/partials-head', [
    'title' => $isEdit ? $c['name'] : 'Yeni Kategori',
    'crumbs' => [['Kategoriler', 'admin/kategoriler'], [$isEdit ? 'Düzenle' : 'Yeni', null]],
]) ?>
<form action="<?= e(url($isEdit ? 'admin/kategoriler/' . $c['id'] : 'admin/kategoriler')) ?>" method="post" enctype="multipart/form-data" class="form-layout">
    <?= csrf_field() ?>
    <div class="panel">
        <div class="panel-body">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Kategori Adı <span class="req">*</span></label><input class="form-control<?= has_error('name') ?>" name="name" value="<?= e($v('name')) ?>" required><?= field_error('name') ?></div>
                <div class="form-group"><label class="form-label">URL (slug)</label><input class="form-control" name="slug" value="<?= e($v('slug')) ?>" placeholder="otomatik"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Üst Kategori</label>
                    <select class="form-control" name="parent_id"><option value="">— Ana kategori —</option>
                        <?php foreach ($parents as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) $v('parent_id') === (int) $p['id'] ? 'selected' : '' ?>><?= $p['parent_id'] ? '— ' : '' ?><?= e($p['name']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="form-group"><label class="form-label">Sıra</label><input class="form-control" type="number" name="sort_order" value="<?= e($v('sort_order', 0)) ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">Açıklama</label><textarea class="form-control" name="description" rows="3"><?= e($v('description')) ?></textarea></div>
            <div class="form-group mb-0"><label class="form-label">İkon</label>
                <div class="icon-picker">
                    <?php foreach ($icons as $ic): ?>
                        <label title="<?= e($ic) ?>"><input type="radio" name="icon" value="<?= e($ic) ?>" <?= $v('icon', 'tag') === $ic ? 'checked' : '' ?>><span><?= icon($ic, '', 20) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-side">
        <div class="panel">
            <div class="panel-body">
                <label class="switch"><input type="checkbox" name="is_active" value="1" <?= (int) $v('is_active', 1) ? 'checked' : '' ?>><span class="track"></span><span>Aktif<small>Mağazada görünür</small></span></label>
                <hr>
                <label class="form-label">Kategori Görseli</label>
                <?php if (!empty($c['image'])): ?>
                    <img src="<?= e(media($c['image'])) ?>" alt="" style="border-radius:12px;margin-bottom:10px">
                    <label class="check mb-1"><input type="checkbox" name="remove_image" value="1"> Görseli kaldır</label>
                <?php endif; ?>
                <input class="form-control" type="file" name="image" accept="image/*" style="padding-top:11px">
            </div>
            <div class="panel-foot"><a href="<?= e(url('admin/kategoriler')) ?>" class="btn btn-ghost btn-sm">Vazgeç</a><button class="btn btn-primary" type="submit"><?= icon('check', '', 17) ?> Kaydet</button></div>
        </div>
    </div>
</form>

<?php /** @var string $inner @var string $heading */ $u = auth(); ?>
<div class="container">
    <div class="page-head">
        <?= partial('partials/breadcrumbs', ['items' => array_merge([['Hesabım', 'hesabim']], isset($crumb) ? [[$crumb, null]] : [])]) ?>
    </div>
    <div class="account-layout">
        <aside class="account-nav card">
            <div class="account-user">
                <span class="avatar"><?= e(initials($u['name'])) ?></span>
                <div><strong><?= e($u['name']) ?></strong><span><?= e($u['email']) ?></span></div>
            </div>
            <nav class="account-menu">
                <a href="<?= e(url('hesabim')) ?>" class="<?= is_active('hesabim') ? 'is-active' : '' ?>"><?= icon('dashboard', '', 19) ?> Genel Bakış</a>
                <a href="<?= e(url('hesabim/siparisler')) ?>" class="<?= is_active('hesabim/siparis*') ? 'is-active' : '' ?>"><?= icon('package', '', 19) ?> Siparişlerim</a>
                <a href="<?= e(url('hesabim/favoriler')) ?>" class="<?= is_active('hesabim/favoriler') ? 'is-active' : '' ?>"><?= icon('heart', '', 19) ?> Favorilerim</a>
                <a href="<?= e(url('hesabim/adresler')) ?>" class="<?= is_active('hesabim/adresler') ? 'is-active' : '' ?>"><?= icon('pin', '', 19) ?> Adreslerim</a>
                <a href="<?= e(url('hesabim/profil')) ?>" class="<?= is_active('hesabim/profil') ? 'is-active' : '' ?>"><?= icon('settings', '', 19) ?> Profil & Şifre</a>
                <?php if ($u['role'] === 'admin'): ?><a href="<?= e(url('admin')) ?>"><?= icon('store', '', 19) ?> Yönetim Paneli</a><?php endif; ?>
                <span class="sep"></span>
                <form action="<?= e(url('cikis')) ?>" method="post"><?= csrf_field() ?><button type="submit"><?= icon('logout', '', 19) ?> Çıkış Yap</button></form>
            </nav>
        </aside>
        <div><?= $inner ?></div>
    </div>
</div>

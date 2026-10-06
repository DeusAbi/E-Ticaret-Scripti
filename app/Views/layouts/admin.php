<?php
use App\Core\Database;
use App\Core\Session;

$u = auth();
$primary = (string) setting('primary_color', '#ef5a24');
$badges = [
    'orders'   => (int) Database::value("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
    'reviews'  => (int) Database::value('SELECT COUNT(*) FROM reviews WHERE is_approved = 0'),
    'messages' => (int) Database::value('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
];
$flashes = [];
foreach (['success', 'error', 'warning', 'info'] as $type) {
    if ($msg = Session::getFlash($type)) {
        $flashes[] = ['type' => $type, 'message' => $msg];
    }
}
$nav = [
    'Genel' => [
        ['admin', 'dashboard', 'Kontrol Paneli', null, 'admin'],
        ['admin/siparisler', 'package', 'Siparişler', $badges['orders'], 'admin/siparisler*'],
        ['admin/musteriler', 'users', 'Müşteriler', null, 'admin/musteriler*'],
    ],
    'Katalog' => [
        ['admin/urunler', 'tag', 'Ürünler', null, 'admin/urunler*'],
        ['admin/kategoriler', 'layers', 'Kategoriler', null, 'admin/kategoriler*'],
        ['admin/yorumlar', 'star', 'Yorumlar', $badges['reviews'], 'admin/yorumlar*'],
    ],
    'Pazarlama & İçerik' => [
        ['admin/kuponlar', 'percent', 'Kuponlar', null, 'admin/kuponlar*'],
        ['admin/slider', 'image', 'Ana Sayfa Slider', null, 'admin/slider*'],
        ['admin/sayfalar', 'file', 'Sayfalar', null, 'admin/sayfalar*'],
        ['admin/mesajlar', 'mail', 'Mesajlar', $badges['messages'], 'admin/mesajlar*'],
    ],
    'Sistem' => [
        ['admin/ayarlar', 'settings', 'Mağaza Ayarları', null, 'admin/ayarlar*'],
    ],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(($pageTitle ?? 'Yönetim') . ' | ' . setting('store_name') . ' Yönetim') ?></title>
    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <style>:root{--primary:<?= e($primary) ?>}</style>
</head>
<body class="admin">
<div class="admin-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="<?= e(url('admin')) ?>" class="logo"><span class="logo-mark"><?= icon('bag', '', 19, 2.2) ?></span><span><?= e(setting('store_name')) ?><small>Yönetim Paneli</small></span></a>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($nav as $group => $items): ?>
                <h6><?= e($group) ?></h6>
                <?php foreach ($items as [$href, $ic, $label, $badge, $pattern]): ?>
                    <a href="<?= e(url($href)) ?>" class="<?= is_active($pattern) ? 'is-active' : '' ?>"><?= icon($ic, '', 19) ?> <?= e($label) ?><?php if ($badge): ?><span class="pill"><?= $badge ?></span><?php endif; ?></a>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <h6>Mağaza</h6>
            <a href="<?= e(url('/')) ?>" target="_blank"><?= icon('external', '', 19) ?> Mağazayı Görüntüle</a>
        </nav>
        <div class="sidebar-foot">
            <div class="sidebar-user">
                <span class="avatar"><?= e(initials($u['name'])) ?></span>
                <div><strong><?= e($u['name']) ?></strong><span>Yönetici</span></div>
                <form action="<?= e(url('cikis')) ?>" method="post"><?= csrf_field() ?><button type="submit" title="Çıkış yap" aria-label="Çıkış yap"><?= icon('logout', '', 18) ?></button></form>
            </div>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="icon-btn sidebar-toggle" data-sidebar-open aria-label="Menü"><?= icon('menu', '', 20) ?></button>
            <form class="admin-search" action="<?= e(url('admin/siparisler')) ?>" method="get">
                <?= icon('search', '', 18) ?>
                <input type="search" name="q" placeholder="Sipariş no, müşteri adı veya e-posta ara…" value="<?= e(is_active('admin/siparisler') ? ($_GET['q'] ?? '') : '') ?>">
            </form>
            <div class="topbar-actions">
                <a href="<?= e(url('admin/siparisler?durum=pending')) ?>" class="icon-btn" title="Bekleyen siparişler"><?= icon('bell', '', 19) ?><?php if ($badges['orders']): ?><span class="dot"></span><?php endif; ?></a>
                <a href="<?= e(url('admin/urunler/yeni')) ?>" class="btn btn-primary btn-sm"><?= icon('plus', '', 16) ?> <span>Yeni Ürün</span></a>
            </div>
        </header>
        <main class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>
<div class="toasts" data-toasts aria-live="polite"></div>
<script>window.APP = <?= json_encode(['base' => base_url_path(), 'csrf' => csrf_token(), 'flashes' => $flashes], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>

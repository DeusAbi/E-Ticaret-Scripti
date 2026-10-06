<?php
use App\Core\Database;
use App\Core\Session;
use App\Services\Cart;
use App\Services\Category;

$storeName = (string) setting('store_name', 'Vitrin');
$navCategories = Category::tree();
$footerPages = Database::all('SELECT title, slug FROM pages WHERE is_active = 1 AND show_in_footer = 1 ORDER BY sort_order, id');
$cartCount = Cart::count();
$user = auth();
$title = isset($pageTitle) ? $pageTitle . ' | ' . $storeName : $storeName . ' — ' . setting('store_tagline');
$metaDesc = $metaDescription ?? setting('meta_description');
$primary = (string) setting('primary_color', '#ef5a24');
$flashes = [];
foreach (['success', 'error', 'warning', 'info'] as $type) {
    if ($msg = Session::getFlash($type)) {
        $flashes[] = ['type' => $type, 'message' => $msg];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="theme-color" content="<?= e($primary) ?>">
    <meta property="og:site_name" content="<?= e($storeName) ?>">
    <meta property="og:title" content="<?= e($pageTitle ?? $storeName) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e(absolute_url($ogImage)) ?>"><?php endif; ?>
    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <style>:root{--primary:<?= e($primary) ?>}</style>
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><linearGradient id="half"><stop offset="50%" stop-color="#f5a524"/><stop offset="50%" stop-color="#e2e5eb"/></linearGradient></defs></svg>

<div class="topbar">
    <div class="container">
        <div class="topbar-promo"><?= icon('truck', '', 16) ?> <?= e(setting('top_bar_text')) ?></div>
        <div class="topbar-links">
            <a href="<?= e(url('siparis-takip')) ?>"><?= icon('package', '', 14) ?> Sipariş Takibi</a>
            <a href="<?= e(url('sayfa/sikca-sorulan-sorular')) ?>"><?= icon('info', '', 14) ?> Yardım</a>
            <a href="<?= e(url('iletisim')) ?>"><?= icon('phone', '', 14) ?> <?= e(setting('store_phone')) ?></a>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="container header-main">
        <button class="btn btn-ghost btn-icon menu-toggle" data-drawer-open aria-label="Menüyü aç"><?= icon('menu', '', 22) ?></button>
        <?= partial('partials/logo') ?>

        <div class="search" data-search>
            <form action="<?= e(url('ara')) ?>" method="get" role="search">
                <?= icon('search', 'icon-search', 19) ?>
                <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Ürün, marka veya kategori ara…" autocomplete="off" aria-label="Ara">
                <button type="submit">Ara</button>
            </form>
            <div class="search-suggest" data-search-results></div>
        </div>

        <nav class="header-actions" aria-label="Kullanıcı menüsü">
            <?php if ($user): ?>
                <div class="dropdown">
                    <a href="<?= e(url('hesabim')) ?>" class="header-action">
                        <?= icon('user', '', 22) ?>
                        <span class="label"><small>Merhaba,</small><strong><?= e(explode(' ', $user['name'])[0]) ?></strong></span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="dropdown-head"><strong><?= e($user['name']) ?></strong><span><?= e($user['email']) ?></span></div>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= e(url('admin')) ?>"><?= icon('dashboard', '', 18) ?> Yönetim Paneli</a>
                        <?php endif; ?>
                        <a href="<?= e(url('hesabim')) ?>"><?= icon('user', '', 18) ?> Hesabım</a>
                        <a href="<?= e(url('hesabim/siparisler')) ?>"><?= icon('package', '', 18) ?> Siparişlerim</a>
                        <a href="<?= e(url('hesabim/favoriler')) ?>"><?= icon('heart', '', 18) ?> Favorilerim</a>
                        <a href="<?= e(url('hesabim/adresler')) ?>"><?= icon('pin', '', 18) ?> Adreslerim</a>
                        <form action="<?= e(url('cikis')) ?>" method="post"><?= csrf_field() ?><button type="submit"><?= icon('logout', '', 18) ?> Çıkış Yap</button></form>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= e(url('giris')) ?>" class="header-action">
                    <?= icon('user', '', 22) ?>
                    <span class="label"><small>Hoş geldiniz</small><strong>Giriş Yap</strong></span>
                </a>
            <?php endif; ?>
            <a href="<?= e(url('hesabim/favoriler')) ?>" class="header-action hide-sm" aria-label="Favorilerim">
                <?= icon('heart', '', 22) ?>
                <span class="label"><small>Listem</small><strong>Favoriler</strong></span>
            </a>
            <div class="dropdown" data-mini-cart-wrap>
                <a href="<?= e(url('sepet')) ?>" class="header-action" aria-label="Sepetim">
                    <?= icon('bag', '', 22) ?>
                    <span class="count" data-cart-count data-count="<?= $cartCount ?>"><?= $cartCount ?: '' ?></span>
                    <span class="label"><small>Alışveriş</small><strong>Sepetim</strong></span>
                </a>
                <div class="dropdown-menu mini-cart" data-mini-cart>
                    <?= partial('partials/mini-cart', ['summary' => $cartCount ? Cart::summary() : null]) ?>
                </div>
            </div>
        </nav>
    </div>

    <nav class="nav-bar" aria-label="Kategoriler">
        <div class="container">
            <?php foreach (array_slice($navCategories, 0, 7) as $cat): ?>
                <div class="nav-item">
                    <a href="<?= e(url('kategori/' . $cat['slug'])) ?>" class="nav-link<?= is_active('kategori/' . $cat['slug']) ? ' is-active' : '' ?>">
                        <?= icon($cat['icon'] ?: 'tag', '', 17) ?> <?= e($cat['name']) ?>
                        <?php if ($cat['children']): ?><?= icon('chevron-down', '', 14) ?><?php endif; ?>
                    </a>
                    <?php if ($cat['children']): ?>
                        <div class="nav-sub">
                            <?php foreach ($cat['children'] as $child): ?>
                                <a href="<?= e(url('kategori/' . $child['slug'])) ?>"><span class="sub-icon"><?= icon($child['icon'] ?: 'tag', '', 17) ?></span> <?= e($child['name']) ?></a>
                            <?php endforeach; ?>
                            <a class="all" href="<?= e(url('kategori/' . $cat['slug'])) ?>">Tüm <?= e($cat['name']) ?> ürünleri <?= icon('arrow-right', '', 15) ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <span class="nav-spacer"></span>
            <a href="<?= e(url('kampanyalar')) ?>" class="nav-link deal<?= is_active('kampanyalar') ? ' is-active' : '' ?>"><?= icon('zap', '', 17) ?> Kampanyalar</a>
        </div>
    </nav>
</header>

<div class="drawer-backdrop" data-drawer-close></div>
<aside class="drawer" aria-label="Mobil menü">
    <div class="drawer-head">
        <?= partial('partials/logo') ?>
        <button class="btn btn-ghost btn-icon" data-drawer-close aria-label="Kapat"><?= icon('x', '', 22) ?></button>
    </div>
    <div class="drawer-body">
        <a href="<?= e(url('/')) ?>"><?= icon('home', '', 20) ?> Ana Sayfa</a>
        <a href="<?= e(url('kampanyalar')) ?>"><?= icon('zap', '', 20) ?> Kampanyalar</a>
        <h6>Kategoriler</h6>
        <?php foreach ($navCategories as $cat): ?>
            <a href="<?= e(url('kategori/' . $cat['slug'])) ?>"><?= icon($cat['icon'] ?: 'tag', '', 20) ?> <?= e($cat['name']) ?></a>
            <?php foreach ($cat['children'] as $child): ?>
                <a class="sub" href="<?= e(url('kategori/' . $child['slug'])) ?>"><?= e($child['name']) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <h6>Yardım</h6>
        <a href="<?= e(url('siparis-takip')) ?>"><?= icon('package', '', 20) ?> Sipariş Takibi</a>
        <a href="<?= e(url('iletisim')) ?>"><?= icon('message', '', 20) ?> İletişim</a>
    </div>
    <div class="drawer-foot">
        <?php if ($user): ?>
            <a href="<?= e(url('hesabim')) ?>" class="btn btn-dark btn-block"><?= icon('user', '', 18) ?> Hesabım</a>
        <?php else: ?>
            <a href="<?= e(url('giris')) ?>" class="btn btn-dark btn-block">Giriş Yap</a>
            <a href="<?= e(url('kayit')) ?>" class="btn btn-outline btn-block">Üye Ol</a>
        <?php endif; ?>
    </div>
</aside>

<main id="main">
    <?= $content ?>
</main>

<section class="newsletter">
    <div class="container">
        <div>
            <h3>Fırsatları kaçırmayın 🎁</h3>
            <p>Bültenimize abone olun, size özel indirim ve kampanyalardan ilk siz haberdar olun.</p>
        </div>
        <form class="newsletter-form" action="<?= e(url('bulten')) ?>" method="post" data-ajax-form>
            <?= csrf_field() ?>
            <input class="form-control" type="email" name="email" placeholder="E-posta adresiniz" required aria-label="E-posta adresiniz">
            <button class="btn btn-primary" type="submit"><?= icon('send', '', 17) ?> Abone Ol</button>
        </form>
    </div>
</section>

<footer class="site-footer">
    <div class="container footer-top">
        <div>
            <?= partial('partials/logo') ?>
            <p><?= e(setting('footer_about')) ?></p>
            <div class="socials">
                <?php foreach (['instagram', 'facebook', 'twitter', 'youtube'] as $s): if ($link = setting('social_' . $s)): ?>
                    <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($s)) ?>"><?= icon($s, '', 18) ?></a>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <div>
            <h4>Kategoriler</h4>
            <ul class="footer-links">
                <?php foreach (array_slice($navCategories, 0, 6) as $cat): ?>
                    <li><a href="<?= e(url('kategori/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Kurumsal</h4>
            <ul class="footer-links">
                <?php foreach ($footerPages as $p): ?>
                    <li><a href="<?= e(url('sayfa/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Müşteri Hizmetleri</h4>
            <ul class="footer-links">
                <li><a href="<?= e(url('hesabim')) ?>">Hesabım</a></li>
                <li><a href="<?= e(url('hesabim/siparisler')) ?>">Siparişlerim</a></li>
                <li><a href="<?= e(url('siparis-takip')) ?>">Sipariş Takibi</a></li>
                <li><a href="<?= e(url('sepet')) ?>">Sepetim</a></li>
                <li><a href="<?= e(url('iletisim')) ?>">İletişim</a></li>
            </ul>
        </div>
        <div>
            <h4>Bize Ulaşın</h4>
            <div class="footer-contact">
                <div><?= icon('phone', '', 18) ?> <a href="tel:<?= e(preg_replace('/\s+/', '', (string) setting('store_phone'))) ?>"><?= e(setting('store_phone')) ?></a></div>
                <div><?= icon('mail', '', 18) ?> <a href="mailto:<?= e(setting('store_email')) ?>"><?= e(setting('store_email')) ?></a></div>
                <div><?= icon('pin', '', 18) ?> <span><?= e(setting('store_address')) ?></span></div>
                <div><?= icon('clock', '', 18) ?> <span>Hafta içi 09:00 – 18:00</span></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e(setting('store_company', $storeName)) ?>. Tüm hakları saklıdır.</span>
            <div class="pay-logos">
                <span class="ssl"><?= icon('lock', '', 15) ?> 256-bit SSL Güvenli Ödeme</span>
                <span class="pay-logo visa">VISA</span>
                <span class="pay-logo mc"><i></i><i></i></span>
                <span class="pay-logo troy">troy</span>
                <span class="pay-logo">AMEX</span>
            </div>
        </div>
    </div>
</footer>

<div class="toasts" data-toasts aria-live="polite"></div>
<script>window.APP = <?= json_encode(['base' => base_url_path(), 'csrf' => csrf_token(), 'flashes' => $flashes], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>

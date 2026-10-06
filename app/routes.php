<?php
declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\Admin;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\ProductController;

/** @var App\Core\Router $router */

// Mağaza
$router->get('/', [HomeController::class, 'index']);
$router->get('/urunler', [ProductController::class, 'index']);
$router->get('/ara', [ProductController::class, 'index']);
$router->get('/kampanyalar', [ProductController::class, 'deals']);
$router->get('/kategori/{slug}', [ProductController::class, 'category']);
$router->get('/urun/{slug}', [ProductController::class, 'show']);
$router->post('/urun/{slug}/yorum', [ProductController::class, 'review']);
$router->get('/api/arama', [ProductController::class, 'suggest']);

// Sepet
$router->get('/sepet', [CartController::class, 'index']);
$router->post('/sepet/ekle', [CartController::class, 'add']);
$router->post('/sepet/guncelle', [CartController::class, 'update']);
$router->post('/sepet/sil', [CartController::class, 'remove']);
$router->post('/sepet/kupon', [CartController::class, 'applyCoupon']);
$router->post('/sepet/kupon/kaldir', [CartController::class, 'removeCoupon']);

// Ödeme
$router->get('/odeme', [CheckoutController::class, 'index']);
$router->post('/odeme', [CheckoutController::class, 'place']);
$router->get('/siparis-tamamlandi/{orderNo}', [CheckoutController::class, 'success']);

// Üyelik
$router->get('/giris', [AuthController::class, 'loginForm']);
$router->post('/giris', [AuthController::class, 'login']);
$router->get('/kayit', [AuthController::class, 'registerForm']);
$router->post('/kayit', [AuthController::class, 'register']);
$router->post('/cikis', [AuthController::class, 'logout']);
$router->get('/sifremi-unuttum', [AuthController::class, 'forgotForm']);
$router->post('/sifremi-unuttum', [AuthController::class, 'forgot']);
$router->get('/sifre-sifirla/{token}', [AuthController::class, 'resetForm']);
$router->post('/sifre-sifirla/{token}', [AuthController::class, 'reset']);

// Hesabım
$router->get('/hesabim', [AccountController::class, 'dashboard']);
$router->get('/hesabim/siparisler', [AccountController::class, 'orders']);
$router->get('/hesabim/siparis/{orderNo}', [AccountController::class, 'order']);
$router->post('/hesabim/siparis/{orderNo}/iptal', [AccountController::class, 'cancelOrder']);
$router->get('/hesabim/adresler', [AccountController::class, 'addresses']);
$router->post('/hesabim/adresler', [AccountController::class, 'saveAddress']);
$router->post('/hesabim/adresler/{id}/sil', [AccountController::class, 'deleteAddress']);
$router->post('/hesabim/adresler/{id}/varsayilan', [AccountController::class, 'defaultAddress']);
$router->get('/hesabim/profil', [AccountController::class, 'profile']);
$router->post('/hesabim/profil', [AccountController::class, 'updateProfile']);
$router->post('/hesabim/sifre', [AccountController::class, 'updatePassword']);
$router->get('/hesabim/favoriler', [AccountController::class, 'wishlist']);
$router->post('/favori', [AccountController::class, 'toggleWishlist']);

// Kurumsal & diğer
$router->get('/sayfa/{slug}', [PageController::class, 'show']);
$router->get('/iletisim', [PageController::class, 'contact']);
$router->post('/iletisim', [PageController::class, 'sendContact']);
$router->get('/siparis-takip', [PageController::class, 'track']);
$router->post('/bulten', [PageController::class, 'newsletter']);

// Yönetim paneli
$router->get('/admin', [Admin\DashboardController::class, 'index']);

$router->get('/admin/urunler', [Admin\ProductController::class, 'index']);
$router->get('/admin/urunler/yeni', [Admin\ProductController::class, 'create']);
$router->post('/admin/urunler', [Admin\ProductController::class, 'store']);
$router->post('/admin/urunler/toplu', [Admin\ProductController::class, 'bulk']);
$router->get('/admin/urunler/{id}/duzenle', [Admin\ProductController::class, 'edit']);
$router->post('/admin/urunler/{id}', [Admin\ProductController::class, 'update']);
$router->post('/admin/urunler/{id}/sil', [Admin\ProductController::class, 'destroy']);
$router->post('/admin/urunler/{id}/kopyala', [Admin\ProductController::class, 'duplicate']);
$router->post('/admin/urun-gorsel/{id}/sil', [Admin\ProductController::class, 'deleteImage']);
$router->post('/admin/urun-gorsel/{id}/kapak', [Admin\ProductController::class, 'coverImage']);

$router->get('/admin/kategoriler', [Admin\CategoryController::class, 'index']);
$router->get('/admin/kategoriler/yeni', [Admin\CategoryController::class, 'create']);
$router->post('/admin/kategoriler', [Admin\CategoryController::class, 'store']);
$router->get('/admin/kategoriler/{id}/duzenle', [Admin\CategoryController::class, 'edit']);
$router->post('/admin/kategoriler/{id}', [Admin\CategoryController::class, 'update']);
$router->post('/admin/kategoriler/{id}/sil', [Admin\CategoryController::class, 'destroy']);

$router->get('/admin/siparisler', [Admin\OrderController::class, 'index']);
$router->get('/admin/siparisler/{id}', [Admin\OrderController::class, 'show']);
$router->get('/admin/siparisler/{id}/yazdir', [Admin\OrderController::class, 'invoice']);
$router->post('/admin/siparisler/{id}/durum', [Admin\OrderController::class, 'updateStatus']);
$router->post('/admin/siparisler/{id}/odeme', [Admin\OrderController::class, 'updatePayment']);
$router->post('/admin/siparisler/{id}/not', [Admin\OrderController::class, 'updateNote']);

$router->get('/admin/musteriler', [Admin\CustomerController::class, 'index']);
$router->get('/admin/musteriler/{id}', [Admin\CustomerController::class, 'show']);
$router->post('/admin/musteriler/{id}/durum', [Admin\CustomerController::class, 'toggle']);
$router->post('/admin/musteriler/{id}/rol', [Admin\CustomerController::class, 'role']);

$router->get('/admin/kuponlar', [Admin\CouponController::class, 'index']);
$router->get('/admin/kuponlar/yeni', [Admin\CouponController::class, 'create']);
$router->post('/admin/kuponlar', [Admin\CouponController::class, 'store']);
$router->get('/admin/kuponlar/{id}/duzenle', [Admin\CouponController::class, 'edit']);
$router->post('/admin/kuponlar/{id}', [Admin\CouponController::class, 'update']);
$router->post('/admin/kuponlar/{id}/sil', [Admin\CouponController::class, 'destroy']);

$router->get('/admin/yorumlar', [Admin\ReviewController::class, 'index']);
$router->post('/admin/yorumlar/{id}/onayla', [Admin\ReviewController::class, 'approve']);
$router->post('/admin/yorumlar/{id}/sil', [Admin\ReviewController::class, 'destroy']);

$router->get('/admin/slider', [Admin\SliderController::class, 'index']);
$router->get('/admin/slider/yeni', [Admin\SliderController::class, 'create']);
$router->post('/admin/slider', [Admin\SliderController::class, 'store']);
$router->get('/admin/slider/{id}/duzenle', [Admin\SliderController::class, 'edit']);
$router->post('/admin/slider/{id}', [Admin\SliderController::class, 'update']);
$router->post('/admin/slider/{id}/sil', [Admin\SliderController::class, 'destroy']);

$router->get('/admin/sayfalar', [Admin\PageController::class, 'index']);
$router->get('/admin/sayfalar/yeni', [Admin\PageController::class, 'create']);
$router->post('/admin/sayfalar', [Admin\PageController::class, 'store']);
$router->get('/admin/sayfalar/{id}/duzenle', [Admin\PageController::class, 'edit']);
$router->post('/admin/sayfalar/{id}', [Admin\PageController::class, 'update']);
$router->post('/admin/sayfalar/{id}/sil', [Admin\PageController::class, 'destroy']);

$router->get('/admin/mesajlar', [Admin\MessageController::class, 'index']);
$router->get('/admin/mesajlar/{id}', [Admin\MessageController::class, 'show']);
$router->post('/admin/mesajlar/{id}/sil', [Admin\MessageController::class, 'destroy']);

$router->get('/admin/ayarlar', [Admin\SettingController::class, 'index']);
$router->post('/admin/ayarlar', [Admin\SettingController::class, 'update']);

<div class="container error-page">
    <div class="error-code"><?= (int) $code ?></div>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <div class="flex gap-1" style="justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('/')) ?>" class="btn btn-primary"><?= icon('home', '', 18) ?> Ana Sayfaya Dön</a>
        <a href="<?= e(url('urunler')) ?>" class="btn btn-outline">Alışverişe Devam Et</a>
    </div>
</div>

<?php $pages = App\Core\Database::all('SELECT title, slug FROM pages WHERE is_active = 1 ORDER BY sort_order, id'); ?>
<div class="container">
    <div class="page-head"><?= partial('partials/breadcrumbs', ['items' => [[$page['title'], null]]]) ?></div>
    <div class="page-layout">
        <aside class="card account-nav">
            <nav class="account-menu">
                <?php foreach ($pages as $p): ?>
                    <a href="<?= e(url('sayfa/' . $p['slug'])) ?>" class="<?= $p['slug'] === $page['slug'] ? 'is-active' : '' ?>"><?= icon('file', '', 18) ?> <?= e($p['title']) ?></a>
                <?php endforeach; ?>
                <a href="<?= e(url('iletisim')) ?>"><?= icon('message', '', 18) ?> İletişim</a>
            </nav>
        </aside>
        <article class="card page-content">
            <h1><?= e($page['title']) ?></h1>
            <p class="small text-muted">Son güncelleme: <?= e(date_tr($page['updated_at'] ?? $page['created_at'], false)) ?></p>
            <div class="prose mt-3"><?= rich_text($page['content']) ?></div>
        </article>
    </div>
</div>

<?php /** @var array $result */ $param = $param ?? 'sayfa'; ?>
<?php if ($result['pages'] > 1): ?>
    <nav class="pagination" aria-label="Sayfalama">
        <a class="<?= $result['page'] <= 1 ? 'is-disabled' : '' ?>" href="<?= e(query_url([$param => $result['page'] - 1])) ?>" aria-label="Önceki"><?= icon('chevron-left', '', 18) ?></a>
        <?php foreach (paginate_links($result['page'], $result['pages']) as $link): ?>
            <?php if ($link === '…'): ?>
                <span class="dots">…</span>
            <?php else: ?>
                <a class="<?= $link === $result['page'] ? 'is-active' : '' ?>" href="<?= e(query_url([$param => $link])) ?>"><?= $link ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
        <a class="<?= $result['page'] >= $result['pages'] ? 'is-disabled' : '' ?>" href="<?= e(query_url([$param => $result['page'] + 1])) ?>" aria-label="Sonraki"><?= icon('chevron-right', '', 18) ?></a>
    </nav>
<?php endif; ?>

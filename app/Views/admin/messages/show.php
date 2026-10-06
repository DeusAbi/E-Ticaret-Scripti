<?= partial('admin/partials-head', ['title' => $message['subject'], 'crumbs' => [['Mesajlar', 'admin/mesajlar'], ['Mesaj', null]]]) ?>
<div class="order-grid">
    <div class="panel"><div class="panel-body" style="white-space:pre-wrap;font-size:15px;line-height:1.7"><?= e($message['message']) ?></div></div>
    <div class="panel">
        <div class="panel-body">
            <div class="customer-box"><span class="avatar"><?= e(initials($message['name'])) ?></span><div><strong><?= e($message['name']) ?></strong><span><?= e(date_tr($message['created_at'])) ?></span></div></div>
            <div class="kv"><div><span>E-posta</span><strong><?= e($message['email']) ?></strong></div><div><span>Telefon</span><strong><?= e($message['phone'] ?: '—') ?></strong></div></div>
        </div>
        <div class="panel-foot">
            <form action="<?= e(url('admin/mesajlar/' . $message['id'] . '/sil')) ?>" method="post" data-confirm="Mesajı silmek istiyor musunuz?"><?= csrf_field() ?><button class="btn btn-danger btn-sm"><?= icon('trash', '', 15) ?> Sil</button></form>
            <a class="btn btn-primary btn-sm" href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject']) ?>"><?= icon('send', '', 15) ?> Yanıtla</a>
        </div>
    </div>
</div>

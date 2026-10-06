<?php $primary = (string) setting('primary_color', '#ef5a24'); ?>
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="utf-8"><title><?= e($title) ?></title></head>
<body style="margin:0;background:#f4f5f8;font-family:Arial,Helvetica,sans-serif;color:#3b4150">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f8;padding:32px 12px">
    <tr><td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden">
            <tr><td style="background:#12141c;padding:24px 32px;color:#fff;font-size:22px;font-weight:bold"><?= e(setting('store_name')) ?><span style="color:<?= e($primary) ?>">.</span></td></tr>
            <tr><td style="padding:32px">
                <h1 style="margin:0 0 16px;font-size:22px;color:#12141c"><?= e($title) ?></h1>
                <div style="font-size:15px;line-height:1.6"><?= $body ?></div>
            </td></tr>
            <tr><td style="padding:20px 32px;background:#fafbfc;font-size:12px;color:#9aa1ae;border-top:1px solid #eef0f4">
                <?= e(setting('store_company')) ?> · <?= e(setting('store_address')) ?><br><?= e(setting('store_phone')) ?> · <?= e(setting('store_email')) ?>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>

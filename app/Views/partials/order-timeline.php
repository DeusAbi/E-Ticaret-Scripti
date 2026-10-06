<?php
use App\Services\Order;
/** @var array $order @var array $history */
$times = [];
foreach ($history as $h) { $times[$h['status']] ??= $h['created_at']; }
$currentIndex = array_search($order['status'], Order::TIMELINE, true);
$labels = ['pending' => 'Sipariş Alındı', 'processing' => 'Hazırlanıyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim Edildi'];
?>
<?php if (in_array($order['status'], ['cancelled', 'refunded'], true)): ?>
    <div class="alert alert-error mb-0"><?= icon('x', '', 18) ?> <span>Bu sipariş <strong><?= e(Order::statusLabel($order['status'])) ?></strong>. <?= isset($times[$order['status']]) ? e(date_tr($times[$order['status']])) : '' ?></span></div>
<?php else: ?>
    <div class="timeline">
        <?php foreach (Order::TIMELINE as $i => $st): ?>
            <div class="timeline-step <?= $i < $currentIndex || ($st === 'delivered' && $order['status'] === 'delivered') ? 'is-done' : ($i === $currentIndex ? 'is-current' : '') ?>">
                <span class="dot"><?= icon(Order::STATUSES[$st]['icon'], '', 18) ?></span>
                <?= e($labels[$st]) ?>
                <?php if (isset($times[$st])): ?><small><?= e(date('d.m.Y H:i', strtotime($times[$st]))) ?></small><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

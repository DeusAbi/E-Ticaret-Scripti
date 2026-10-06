<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class Coupon
{
    /**
     * @return array{0: ?array, 1: ?string} [kupon, hata]
     */
    public static function validate(string $code, float $subtotal): array
    {
        $code = mb_strtoupper(trim($code));
        if ($code === '') {
            return [null, 'Lütfen bir kupon kodu girin.'];
        }
        $coupon = Database::one('SELECT * FROM coupons WHERE code = ?', [$code]);
        if (!$coupon || !(int) $coupon['is_active']) {
            return [null, 'Geçersiz kupon kodu.'];
        }
        $now = now();
        if ($coupon['starts_at'] && $coupon['starts_at'] > $now) {
            return [null, 'Bu kupon henüz kullanıma açılmadı.'];
        }
        if ($coupon['expires_at'] && $coupon['expires_at'] < $now) {
            return [null, 'Bu kuponun süresi dolmuş.'];
        }
        if ((int) $coupon['max_uses'] > 0 && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
            return [null, 'Bu kuponun kullanım limiti dolmuş.'];
        }
        if ((float) $coupon['min_total'] > 0 && $subtotal < (float) $coupon['min_total']) {
            return [null, 'Bu kupon en az ' . money($coupon['min_total']) . ' tutarındaki alışverişlerde geçerlidir.'];
        }
        return [$coupon, null];
    }

    public static function discountFor(array $coupon, float $subtotal): float
    {
        $value = (float) $coupon['value'];
        $discount = $coupon['type'] === 'percent' ? $subtotal * $value / 100 : $value;
        return round(min($discount, $subtotal), 2);
    }

    public static function describe(array $coupon): string
    {
        return $coupon['type'] === 'percent'
            ? '%' . rtrim(rtrim(number_format((float) $coupon['value'], 2, ',', ''), '0'), ',') . ' indirim'
            : money($coupon['value']) . ' indirim';
    }
}

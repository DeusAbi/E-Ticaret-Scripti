<?php
declare(strict_types=1);

namespace App\Services;

final class Pricing
{
    public static function shippingFee(float $amount, bool $hasItems = true): float
    {
        if (!$hasItems) {
            return 0.0;
        }
        $threshold = self::freeShippingThreshold();
        if ($threshold > 0 && $amount >= $threshold) {
            return 0.0;
        }
        return (float) setting('shipping_fee', 0);
    }

    public static function freeShippingThreshold(): float
    {
        return (float) setting('free_shipping_threshold', 0);
    }

    public static function codFee(): float
    {
        return (float) setting('cod_fee', 0);
    }

    /**
     * Fiyatlar KDV dahil kabul edilir; içerdiği KDV tutarını hesaplar.
     */
    public static function includedTax(array $items, float $discount = 0.0): float
    {
        $subtotal = array_sum(array_column($items, 'total'));
        if ($subtotal <= 0) {
            return 0.0;
        }
        $ratio = max(0, ($subtotal - $discount) / $subtotal);
        $tax = 0.0;
        foreach ($items as $item) {
            $rate = (float) ($item['product']['tax_rate'] ?? 20);
            $gross = $item['total'] * $ratio;
            $tax += $gross - $gross / (1 + $rate / 100);
        }
        return round($tax, 2);
    }

    public static function discountPercent(float $price, ?float $compare): int
    {
        if (!$compare || $compare <= $price) {
            return 0;
        }
        return (int) round(($compare - $price) / $compare * 100);
    }
}

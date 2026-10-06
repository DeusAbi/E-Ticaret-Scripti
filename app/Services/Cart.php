<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;

/**
 * Oturum tabanlı alışveriş sepeti.
 * Fiyat ve stok bilgisi her zaman veritabanından güncel okunur.
 */
final class Cart
{
    private const KEY = 'cart';
    private const MAX_QTY = 99;

    /** @return array<int,int> ürün_id => adet */
    public static function raw(): array
    {
        return array_map('intval', (array) Session::get(self::KEY, []));
    }

    private static function save(array $items): void
    {
        Session::set(self::KEY, array_filter($items, static fn ($q) => $q > 0));
    }

    public static function count(): int
    {
        return array_sum(self::raw());
    }

    public static function quantityOf(int $productId): int
    {
        return self::raw()[$productId] ?? 0;
    }

    /**
     * @return string|null Hata mesajı veya null
     */
    public static function add(int $productId, int $qty = 1): ?string
    {
        $product = Product::findActive($productId);
        if (!$product) {
            return 'Ürün bulunamadı veya satışta değil.';
        }
        $items = self::raw();
        $newQty = ($items[$productId] ?? 0) + max(1, $qty);
        $stock = (int) $product['stock'];
        if ($stock <= 0) {
            return 'Bu ürün şu anda stokta yok.';
        }
        if ($newQty > $stock) {
            $items[$productId] = $stock;
            self::save($items);
            return "Bu üründen en fazla $stock adet satın alabilirsiniz.";
        }
        $items[$productId] = min($newQty, self::MAX_QTY);
        self::save($items);
        return null;
    }

    public static function update(int $productId, int $qty): ?string
    {
        $items = self::raw();
        if (!isset($items[$productId])) {
            return null;
        }
        if ($qty <= 0) {
            unset($items[$productId]);
            self::save($items);
            return null;
        }
        $product = Product::findActive($productId);
        $stock = (int) ($product['stock'] ?? 0);
        $error = null;
        if ($qty > $stock) {
            $qty = $stock;
            $error = "Stokta yalnızca $stock adet bulunuyor.";
        }
        $items[$productId] = min($qty, self::MAX_QTY);
        self::save($items);
        return $error;
    }

    public static function remove(int $productId): void
    {
        $items = self::raw();
        unset($items[$productId]);
        self::save($items);
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
        Session::forget('coupon');
    }

    /**
     * Sepet satırları (güncel fiyat & stok ile)
     * @return array{items: array, warnings: string[]}
     */
    public static function items(): array
    {
        $raw = self::raw();
        if (!$raw) {
            return ['items' => [], 'warnings' => []];
        }
        $ids = array_keys($raw);
        $rows = Database::all(
            'SELECT p.*, (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image
             FROM products p WHERE p.id IN (' . Database::placeholders(count($ids)) . ')',
            $ids
        );
        $byId = array_column($rows, null, 'id');

        $items = [];
        $warnings = [];
        $changed = false;
        foreach ($raw as $id => $qty) {
            $p = $byId[$id] ?? null;
            if (!$p || $p['status'] !== 'active') {
                unset($raw[$id]);
                $changed = true;
                $warnings[] = 'Sepetinizdeki bir ürün artık satışta olmadığı için kaldırıldı.';
                continue;
            }
            $stock = (int) $p['stock'];
            if ($stock <= 0) {
                unset($raw[$id]);
                $changed = true;
                $warnings[] = "\"{$p['name']}\" stokta kalmadığı için sepetten çıkarıldı.";
                continue;
            }
            if ($qty > $stock) {
                $qty = $stock;
                $raw[$id] = $stock;
                $changed = true;
                $warnings[] = "\"{$p['name']}\" için adet, mevcut stok ($stock) ile sınırlandı.";
            }
            $price = (float) $p['price'];
            $items[] = [
                'product'  => $p,
                'id'       => (int) $id,
                'qty'      => $qty,
                'price'    => $price,
                'total'    => round($price * $qty, 2),
            ];
        }
        if ($changed) {
            self::save($raw);
        }
        return ['items' => $items, 'warnings' => array_values(array_unique($warnings))];
    }

    public static function applyCoupon(string $code): ?string
    {
        $summary = self::summary(false);
        [$coupon, $error] = Coupon::validate($code, $summary['subtotal']);
        if ($error) {
            return $error;
        }
        Session::set('coupon', $coupon['code']);
        return null;
    }

    public static function removeCoupon(): void
    {
        Session::forget('coupon');
    }

    /**
     * Sepet özeti: ara toplam, indirim, kargo, genel toplam
     */
    public static function summary(bool $withCoupon = true, string $paymentMethod = ''): array
    {
        ['items' => $items, 'warnings' => $warnings] = self::items();
        $subtotal = round(array_sum(array_column($items, 'total')), 2);

        $discount = 0.0;
        $coupon = null;
        $couponError = null;
        $code = Session::get('coupon');
        if ($withCoupon && $code) {
            [$coupon, $couponError] = Coupon::validate((string) $code, $subtotal);
            if ($couponError) {
                Session::forget('coupon');
                $warnings[] = "Kupon kaldırıldı: $couponError";
                $coupon = null;
            } else {
                $discount = Coupon::discountFor($coupon, $subtotal);
            }
        }

        $afterDiscount = max(0, $subtotal - $discount);
        $shipping = Pricing::shippingFee($afterDiscount, $items !== []);
        $paymentFee = $paymentMethod === 'cod' ? Pricing::codFee() : 0.0;
        $total = round($afterDiscount + $shipping + $paymentFee, 2);
        $freeThreshold = Pricing::freeShippingThreshold();

        return [
            'items'          => $items,
            'warnings'       => $warnings,
            'count'          => array_sum(array_column($items, 'qty')),
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'coupon'         => $coupon,
            'shipping'       => $shipping,
            'payment_fee'    => $paymentFee,
            'total'          => $total,
            'tax_included'   => Pricing::includedTax($items, $discount),
            'free_threshold' => $freeThreshold,
            'free_remaining' => $freeThreshold > 0 && $items ? max(0, round($freeThreshold - $afterDiscount, 2)) : 0,
        ];
    }
}

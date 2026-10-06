<?php
declare(strict_types=1);

namespace App\Payment;

final class Gateway
{
    public static function make(): PaymentGatewayInterface
    {
        return match ((string) config('payment.gateway', 'demo')) {
            // 'iyzico' => new IyzicoGateway(),
            default => new DemoGateway(),
        };
    }

    /** Kart bilgilerini doğrular. Hata varsa mesaj döner. */
    public static function validateCard(array $card): ?string
    {
        $number = preg_replace('/\D+/', '', (string) ($card['number'] ?? ''));
        if (trim((string) ($card['holder'] ?? '')) === '') {
            return 'Kart üzerindeki ismi girin.';
        }
        if (strlen($number) < 15 || strlen($number) > 19 || !self::luhn($number)) {
            return 'Kart numarası geçersiz.';
        }
        $month = (int) ($card['exp_month'] ?? 0);
        $year = (int) ($card['exp_year'] ?? 0);
        if ($year < 100) {
            $year += 2000;
        }
        if ($month < 1 || $month > 12 || $year < (int) date('Y') || ($year === (int) date('Y') && $month < (int) date('n'))) {
            return 'Kartın son kullanma tarihi geçersiz.';
        }
        if (!preg_match('/^\d{3,4}$/', (string) ($card['cvc'] ?? ''))) {
            return 'Güvenlik kodu (CVC) geçersiz.';
        }
        return null;
    }

    public static function luhn(string $number): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }
}

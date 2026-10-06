<?php
declare(strict_types=1);

namespace App\Payment;

/**
 * Test amaçlı sanal POS. Gerçek tahsilat yapmaz, kart bilgisi saklanmaz.
 *
 *  - 4242 4242 4242 4242 (veya Luhn kontrolünden geçen herhangi bir kart) => Başarılı
 *  - 4000 0000 0000 0002                                                   => Banka reddi
 */
final class DemoGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'Demo Sanal POS';
    }

    public function charge(array $order, array $card): PaymentResult
    {
        $number = preg_replace('/\D+/', '', (string) $card['number']);
        if ($number === '4000000000000002') {
            return PaymentResult::fail('Kartınız banka tarafından reddedildi. Lütfen farklı bir kart deneyin.');
        }
        if ((float) $order['total'] <= 0) {
            return PaymentResult::fail('Geçersiz ödeme tutarı.');
        }
        return PaymentResult::ok('DEMO-' . strtoupper(bin2hex(random_bytes(6))));
    }
}

<?php
declare(strict_types=1);

namespace App\Payment;

/**
 * Sanal POS entegrasyonları için ortak arayüz.
 * iyzico, PayTR, Param vb. sağlayıcılar bu arayüzü uygulayarak sisteme eklenebilir.
 */
interface PaymentGatewayInterface
{
    /**
     * @param array $order  Sipariş satırı (orders tablosu)
     * @param array $card   ['holder','number','exp_month','exp_year','cvc']
     */
    public function charge(array $order, array $card): PaymentResult;

    public function name(): string;
}

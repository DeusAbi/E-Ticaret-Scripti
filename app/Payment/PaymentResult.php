<?php
declare(strict_types=1);

namespace App\Payment;

final class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $reference = null,
        public readonly ?string $message = null,
    ) {
    }

    public static function ok(string $reference): self
    {
        return new self(true, $reference);
    }

    public static function fail(string $message): self
    {
        return new self(false, null, $message);
    }
}

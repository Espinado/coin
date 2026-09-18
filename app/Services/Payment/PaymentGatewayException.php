<?php

namespace App\Services\Payment;

use RuntimeException;

class PaymentGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly mixed $gatewayValue = null,
    ) {
        parent::__construct($message);
    }
}

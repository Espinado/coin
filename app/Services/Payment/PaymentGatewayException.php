<?php

namespace App\Services\Payment;

use App\Support\CcapiUserMessage;
use RuntimeException;

class PaymentGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly mixed $gatewayValue = null,
    ) {
        parent::__construct($message);
    }

    public function userMessage(): string
    {
        return CcapiUserMessage::fromGatewayException($this);
    }
}

<?php

namespace App\Services\Payment\Dtos;

use Carbon\CarbonInterface;

readonly class DepositIntentDto
{
    public function __construct(
        public string $paymentAddress,
        public string $gatewayUniqId,
        public string $gatewayNetwork,
        public ?CarbonInterface $expiresAt = null,
        public ?string $qrCodeUrl = null,
        public array $raw = [],
    ) {}
}

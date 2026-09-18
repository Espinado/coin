<?php

namespace App\Services\Payment\Dtos;

readonly class PayoutRequestDto
{
    public function __construct(
        public string $gatewayRequestId,
        public string $gatewayUniqId,
        public array $raw = [],
    ) {}
}

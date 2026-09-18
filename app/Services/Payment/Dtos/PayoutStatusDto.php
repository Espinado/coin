<?php

namespace App\Services\Payment\Dtos;

readonly class PayoutStatusDto
{
    public function __construct(
        public string $gatewayRequestId,
        public string $state,
        public ?string $txid = null,
        public ?string $result = null,
        public array $raw = [],
    ) {}

    public function isConfirmed(): bool
    {
        return $this->state === '7';
    }

    public function isFailed(): bool
    {
        return in_array($this->state, ['8', '9'], true);
    }
}

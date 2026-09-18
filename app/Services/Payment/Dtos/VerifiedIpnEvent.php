<?php

namespace App\Services\Payment\Dtos;

readonly class VerifiedIpnEvent
{
    public function __construct(
        public string $type,
        public string $chain,
        public ?string $currency,
        public ?string $token,
        public ?float $amount,
        public ?string $txid,
        public int $pos,
        public int $confirmation,
        public ?string $label,
        public ?string $gatewayRequestId,
        public array $raw,
    ) {}

    public function idempotencyKey(): string
    {
        if ($this->txid !== null && $this->txid !== '') {
            return $this->txid.':'.$this->pos;
        }

        return ($this->label ?? 'unknown').':'.($this->gatewayRequestId ?? $this->type);
    }

    public function isIncomingPayment(): bool
    {
        return $this->type === 'in';
    }

    public function isOutgoingPayment(): bool
    {
        return in_array($this->type, ['out', 'outTx'], true);
    }

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        return new self(
            type: (string) ($payload['type'] ?? ''),
            chain: (string) ($payload['chain'] ?? ''),
            currency: isset($payload['currency']) ? (string) $payload['currency'] : null,
            token: isset($payload['token']) && $payload['token'] !== '' ? (string) $payload['token'] : null,
            amount: isset($payload['amount']) ? (float) $payload['amount'] : null,
            txid: isset($payload['txid']) && $payload['txid'] !== '' ? (string) $payload['txid'] : null,
            pos: (int) ($payload['pos'] ?? 0),
            confirmation: (int) ($payload['confirmation'] ?? 0),
            label: isset($payload['label']) && $payload['label'] !== '' ? (string) $payload['label'] : null,
            gatewayRequestId: isset($payload['id']) && $payload['id'] !== '' ? (string) $payload['id'] : null,
            raw: $payload,
        );
    }
}

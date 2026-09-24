<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\Withdrawal;

class CcapiIpnPayloadBuilder
{
    public function __construct(
        private readonly CcapiIpnVerifier $verifier,
    ) {}

    /** @return array<string, mixed> */
    public function forDeposit(Deposit $deposit, ?string $txid = null, int $confirmation = 1, ?float $amount = null): array
    {
        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => $this->chainForNetwork($deposit->gateway_network),
            'currency' => strtoupper($deposit->currency) === 'BTC' ? 'BTC' : 'TRX',
            'type' => 'in',
            'date' => now()->timestamp,
            'from' => '',
            'to' => $deposit->payment_address ?? '',
            'token' => strtoupper($deposit->currency) === 'BTC' ? '' : strtoupper($deposit->currency),
            'amount' => number_format($amount ?? (float) $deposit->amount, 6, '.', ''),
            'fee' => '0.000000',
            'txid' => $txid ?? 'sim-'.bin2hex(random_bytes(16)),
            'pos' => 0,
            'confirmation' => $confirmation,
            'label' => Deposit::gatewayUniqId($deposit->id),
        ];

        if ($payload['token'] === '') {
            unset($payload['token']);
        }

        return $this->sign($payload);
    }

    /** @return array<string, mixed> */
    public function forWithdrawal(Withdrawal $withdrawal, ?string $txid = null, int $confirmation = 7, ?float $amount = null): array
    {
        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'out',
            'date' => now()->timestamp,
            'to' => $withdrawal->payout_address,
            'token' => strtoupper($withdrawal->currency) === 'BTC' ? '' : strtoupper($withdrawal->currency),
            'amount' => number_format($amount ?? (float) $withdrawal->amount, 6, '.', ''),
            'fee' => '0.000000',
            'txid' => $txid ?? 'sim-'.bin2hex(random_bytes(16)),
            'pos' => 0,
            'confirmation' => $confirmation,
            'label' => Withdrawal::gatewayUniqId($withdrawal->reference),
            'id' => $withdrawal->gateway_request_id ?? 'sim-'.random_int(10000, 99999),
        ];

        if ($payload['token'] === '') {
            unset($payload['token']);
        }

        return $this->sign($payload);
    }

    /** @param array<string, mixed> $payload */
    public function sign(array $payload): array
    {
        $apiKey = (string) config('coin.payments.ccapi.api_key', '');

        if ($apiKey === '') {
            if (! app()->environment(['local', 'testing'])) {
                throw new \RuntimeException('CCAPI_API_KEY is not configured.');
            }

            $apiKey = 'local-sim-key';
        }

        $payload['sign'] = $this->verifier->sign($payload, $apiKey);

        return $payload;
    }

    private function chainForNetwork(?string $network): string
    {
        return match ($network) {
            'btc' => 'bitcoin',
            'eth' => 'ethereum',
            'bnb' => 'bsc',
            default => 'tron',
        };
    }
}

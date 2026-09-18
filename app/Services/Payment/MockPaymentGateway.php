<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\Payment\Dtos\DepositIntentDto;
use App\Services\Payment\Dtos\PayoutRequestDto;
use App\Services\Payment\Dtos\PayoutStatusDto;
use App\Services\Payment\Dtos\VerifiedIpnEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly CcapiIpnVerifier $ipnVerifier,
    ) {}

    public function createDepositIntent(Deposit $deposit): DepositIntentDto
    {
        $uniqId = Deposit::gatewayUniqId($deposit->id);
        $period = (int) config('coin.payments.ccapi.deposit_period_minutes', 60);

        return new DepositIntentDto(
            paymentAddress: 'MOCK-'.Str::upper(Str::random(16)),
            gatewayUniqId: $uniqId,
            gatewayNetwork: 'mock',
            expiresAt: Carbon::now()->addMinutes($period),
            raw: ['mock' => true],
        );
    }

    public function sendPayout(Withdrawal $withdrawal): PayoutRequestDto
    {
        $uniqId = Withdrawal::gatewayUniqId($withdrawal->reference);

        return new PayoutRequestDto(
            gatewayRequestId: 'MOCK-'.random_int(10000, 99999),
            gatewayUniqId: $uniqId,
            raw: ['mock' => true],
        );
    }

    public function getPayoutStatus(Withdrawal $withdrawal): PayoutStatusDto
    {
        return new PayoutStatusDto(
            gatewayRequestId: $withdrawal->gateway_request_id ?? 'MOCK-0',
            state: '7',
            txid: 'mock-txid-'.Str::lower(Str::random(12)),
            raw: ['mock' => true],
        );
    }

    public function verifyIpn(Request $request): VerifiedIpnEvent
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || $payload === []) {
            throw new PaymentGatewayException('mock_ipn_empty_payload');
        }

        $apiKey = (string) config('coin.payments.ccapi.api_key', 'mock-api-key');

        if ($apiKey !== '' && isset($payload['sign']) && ! $this->ipnVerifier->verify($payload, $apiKey)) {
            throw new PaymentGatewayException('mock_ipn_invalid_signature');
        }

        return VerifiedIpnEvent::fromPayload($payload);
    }
}

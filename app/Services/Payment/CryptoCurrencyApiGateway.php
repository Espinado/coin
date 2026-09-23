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

class CryptoCurrencyApiGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly CryptoCurrencyApiClient $client,
        private readonly CcapiIpnVerifier $ipnVerifier,
    ) {}

    public function createDepositIntent(Deposit $deposit): DepositIntentDto
    {
        $network = $this->networkForCurrency($deposit->currency);
        $uniqId = Deposit::gatewayUniqId($deposit->id);
        $period = (int) config('coin.payments.ccapi.deposit_period_minutes', 60);

        $params = [
            'label' => $uniqId,
            'uniqID' => $uniqId,
            'period' => $period,
            'statusURL' => (string) config('coin.payments.ccapi.ipn_url'),
        ];

        if ($network['token'] !== '') {
            $params['token'] = $network['token'];
        }

        $params = array_merge($params, $this->giveForwardParams());

        $result = $this->client->call($network['network'], '.give', $params);

        if (! is_array($result) || empty($result['address'])) {
            throw new PaymentGatewayException('ccapi_give_invalid_response', $result);
        }

        return new DepositIntentDto(
            paymentAddress: (string) $result['address'],
            gatewayUniqId: $uniqId,
            gatewayNetwork: $network['network'],
            expiresAt: Carbon::now()->addMinutes($period),
            qrCodeUrl: isset($result['qr']) ? (string) $result['qr'] : null,
            raw: $result,
        );
    }

    public function sendPayout(Withdrawal $withdrawal): PayoutRequestDto
    {
        $network = $this->networkForCurrency($withdrawal->currency);
        $uniqId = Withdrawal::gatewayUniqId($withdrawal->reference);

        $params = [
            'to' => $withdrawal->payout_address,
            'amount' => number_format((float) $withdrawal->amount, 8, '.', ''),
            'label' => $uniqId,
            'uniqID' => $uniqId,
            'statusURL' => (string) config('coin.payments.ccapi.ipn_url'),
        ];

        if ($network['token'] !== '') {
            $params['token'] = $network['token'];
        }

        $result = $this->client->call($network['network'], '.send', $params);

        if (! is_string($result) && ! is_int($result)) {
            throw new PaymentGatewayException('ccapi_send_invalid_response', $result);
        }

        return new PayoutRequestDto(
            gatewayRequestId: (string) $result,
            gatewayUniqId: $uniqId,
            raw: ['result' => $result],
        );
    }

    public function getPayoutStatus(Withdrawal $withdrawal): PayoutStatusDto
    {
        $network = $this->networkForCurrency($withdrawal->currency);
        $lookupId = $withdrawal->gateway_request_id ?: Withdrawal::gatewayUniqId($withdrawal->reference);

        $result = $this->client->call($network['network'], '.status', [
            'id' => $lookupId,
        ]);

        if (! is_array($result)) {
            throw new PaymentGatewayException('ccapi_status_invalid_response', $result);
        }

        return new PayoutStatusDto(
            gatewayRequestId: (string) ($result['id'] ?? $lookupId),
            state: (string) ($result['state'] ?? ''),
            txid: isset($result['txid']) && $result['txid'] !== '' ? (string) $result['txid'] : null,
            result: isset($result['result']) ? (string) $result['result'] : null,
            raw: $result,
        );
    }

    public function verifyIpn(Request $request): VerifiedIpnEvent
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || $payload === []) {
            throw new PaymentGatewayException('ccapi_ipn_empty_payload');
        }

        $apiKey = (string) config('coin.payments.ccapi.api_key');

        if ($apiKey === '' || ! $this->ipnVerifier->verify($payload, $apiKey)) {
            throw new PaymentGatewayException('ccapi_ipn_invalid_signature');
        }

        return VerifiedIpnEvent::fromPayload($payload);
    }

    /** @return array<string, string> */
    private function giveForwardParams(): array
    {
        $forwardTo = trim((string) config('coin.payments.ccapi.forward_to', ''));

        if ($forwardTo === '') {
            return [];
        }

        $params = ['to' => $forwardTo];

        $forwardFrom = trim((string) config('coin.payments.ccapi.forward_from', ''));

        $params['from'] = $forwardFrom !== '' ? $forwardFrom : $forwardTo;

        return $params;
    }

    /** @return array{network: string, token: string} */
    private function networkForCurrency(string $currency): array
    {
        $currency = strtoupper(trim($currency));
        $networks = config('coin.payments.ccapi.networks', []);

        if (! isset($networks[$currency])) {
            throw new PaymentGatewayException('ccapi_unsupported_currency', $currency);
        }

        return [
            'network' => (string) $networks[$currency]['network'],
            'token' => (string) ($networks[$currency]['token'] ?? ''),
        ];
    }
}

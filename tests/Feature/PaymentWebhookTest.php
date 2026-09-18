<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\PaymentWebhookLog;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Payment\CcapiIpnVerifier;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'test-ccapi-key';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.payments.driver' => 'mock',
            'coin.payments.ccapi.api_key' => $this->apiKey,
            'coin.payments.ccapi.min_confirmations' => 1,
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_ccapi_webhook_confirms_pending_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockAddress123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'abc123tx', 1);

        $response = $this->postJson('http://coin.test/webhooks/ccapi', $payload);

        $response->assertOk()->assertSee('OK');

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_CONFIRMED, $deposit->status);
        $this->assertSame('abc123tx', $deposit->txid);
        $this->assertSame('100.00000000', number_format((float) $deposit->received_amount, 8, '.', ''));
        $this->assertSame('100.00', number_format((float) $user->wallet->available, 2, '.', ''));

        $this->assertDatabaseHas('payment_webhook_logs', [
            'deposit_id' => $deposit->id,
            'event_type' => 'in',
            'signature_valid' => true,
        ]);
    }

    public function test_ccapi_webhook_rejects_invalid_signature(): void
    {
        $payload = [
            'type' => 'in',
            'chain' => 'tron',
            'amount' => '10',
            'confirmation' => 1,
            'label' => 'deposit:1',
            'sign' => 'invalid',
        ];

        $response = $this->postJson('http://coin.test/webhooks/ccapi', $payload);

        $response->assertForbidden();

        $this->assertDatabaseHas('payment_webhook_logs', [
            'signature_valid' => false,
            'event_type' => 'invalid',
        ]);
    }

    public function test_duplicate_ipn_does_not_double_credit_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockAddress456',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'dup-tx-1', 1);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();
        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $user->refresh();

        $this->assertSame('50.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame(1, PaymentWebhookLog::query()
            ->where('processing_result', 'like', 'processed:%')
            ->count());
    }

    public function test_outgoing_ipn_marks_processing_withdrawal_as_paid(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-TEST1234',
            'amount' => 25,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'network_label' => 'TRC-20',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
        ]);

        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'out',
            'date' => now()->timestamp,
            'to' => 'TRecipient123',
            'token' => 'USDT',
            'amount' => '25.000000',
            'fee' => '0.000000',
            'txid' => 'withdraw-tx-1',
            'pos' => 0,
            'confirmation' => 7,
            'label' => Withdrawal::gatewayUniqId($withdrawal->reference),
            'id' => '999',
        ];

        $payload['sign'] = app(CcapiIpnVerifier::class)->sign($payload, $this->apiKey);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->status);
        $this->assertSame('withdraw-tx-1', $withdrawal->txid);
    }

    /** @return array<string, mixed> */
    private function signedDepositPayload(Deposit $deposit, string $txid, int $confirmation): array
    {
        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'in',
            'date' => now()->timestamp,
            'from' => '',
            'to' => $deposit->payment_address,
            'token' => 'USDT',
            'amount' => number_format((float) $deposit->amount, 6, '.', ''),
            'fee' => '0.000000',
            'txid' => $txid,
            'pos' => 0,
            'confirmation' => $confirmation,
            'label' => Deposit::gatewayUniqId($deposit->id),
        ];

        $payload['sign'] = app(CcapiIpnVerifier::class)->sign($payload, $this->apiKey);

        return $payload;
    }
}

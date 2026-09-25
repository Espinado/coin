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
            'coin.payments.driver' => 'ccapi',
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

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'source' => __('coin.tx_sources.live_top_up'),
        ]);
    }

    public function test_sub_threshold_ipn_retry_confirms_deposit_when_confirmations_increase(): void
    {
        config(['coin.payments.ccapi.min_confirmations' => 3]);

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockSubThreshold123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $txid = 'sub-threshold-retry-tx';

        $this->postJson(
            'http://coin.test/webhooks/ccapi',
            $this->signedDepositPayload($deposit, $txid, 1),
        )->assertOk();

        $deposit->refresh();
        $this->assertSame(Deposit::STATUS_PENDING, $deposit->status);

        $this->assertDatabaseHas('payment_webhook_logs', [
            'processing_result' => PaymentWebhookLog::RESULT_IGNORED.': Awaiting confirmations.',
        ]);

        $this->postJson(
            'http://coin.test/webhooks/ccapi',
            $this->signedDepositPayload($deposit, $txid, 3),
        )->assertOk();

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_CONFIRMED, $deposit->status);
        $this->assertSame($txid, $deposit->txid);
        $this->assertSame('50.00', number_format((float) $user->wallet->available, 2, '.', ''));

        $this->assertDatabaseHas('payment_webhook_logs', [
            'deposit_id' => $deposit->id,
            'processing_result' => PaymentWebhookLog::RESULT_PROCESSED.': Deposit confirmed from IPN.',
        ]);
    }

    public function test_duplicate_ipn_does_not_overwrite_processed_webhook_log(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockDuplicate123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'duplicate-tx-1', 1);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();
        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();
        $this->assertSame(Deposit::STATUS_CONFIRMED, $deposit->status);

        $this->assertDatabaseHas('payment_webhook_logs', [
            'deposit_id' => $deposit->id,
            'processing_result' => PaymentWebhookLog::RESULT_PROCESSED.': Deposit confirmed from IPN.',
        ]);

        $this->assertSame(1, PaymentWebhookLog::query()
            ->where('deposit_id', $deposit->id)
            ->excludeDuplicateResults()
            ->count());
    }

    public function test_retry_ipn_after_confirm_restores_unlinked_processed_webhook_log(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockRestoreLink123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $firstPayload = $this->signedDepositPayload($deposit, 'restore-link-tx-1', 1);
        $this->postJson('http://coin.test/webhooks/ccapi', $firstPayload)->assertOk();

        PaymentWebhookLog::query()
            ->where('deposit_id', $deposit->id)
            ->update(['deposit_id' => null]);

        $retryPayload = $this->signedDepositPayload($deposit, 'restore-link-tx-2', 1);
        $this->postJson('http://coin.test/webhooks/ccapi', $retryPayload)->assertOk();

        $this->assertDatabaseHas('payment_webhook_logs', [
            'deposit_id' => $deposit->id,
            'processing_result' => PaymentWebhookLog::RESULT_PROCESSED.': Deposit confirmed from IPN.',
        ]);
    }

    public function test_journal_for_deposit_includes_duplicate_when_no_primary_log_exists(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_CONFIRMED,
            'method' => 'ccapi',
            'payment_address' => 'TMockJournalDup123',
            'gateway_network' => 'trx',
            'txid' => 'journal-dup-tx',
            'confirmed_at' => now(),
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        PaymentWebhookLog::query()->create([
            'gateway' => 'ccapi',
            'event_type' => 'in',
            'payload' => [
                'type' => 'in',
                'label' => $deposit->gateway_uniq_id,
                'txid' => 'journal-dup-tx',
                'confirmation' => 10,
            ],
            'signature_valid' => true,
            'idempotency_key' => 'journal-dup-tx:0',
            'deposit_id' => $deposit->id,
            'processing_result' => PaymentWebhookLog::RESULT_DUPLICATE.': Deposit already confirmed.',
            'processed_at' => now(),
        ]);

        $journal = PaymentWebhookLog::journalForDeposit($deposit);

        $this->assertCount(1, $journal);
        $this->assertTrue($journal->first()?->isDuplicateResult());
        $this->assertSame(0, PaymentWebhookLog::query()->linkedToDeposit($deposit)->excludeDuplicateResults()->count());
    }

    public function test_linked_to_deposit_scope_finds_processed_webhook_without_deposit_id(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockScope123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $this->postJson('http://coin.test/webhooks/ccapi', $this->signedDepositPayload($deposit, 'scope-tx-1', 1))->assertOk();

        $deposit->refresh();
        PaymentWebhookLog::query()
            ->where('payload->txid', 'scope-tx-1')
            ->update(['deposit_id' => null]);

        $this->assertSame(1, PaymentWebhookLog::query()
            ->linkedToDeposit($deposit)
            ->excludeDuplicateResults()
            ->count());
    }

    public function test_same_txid_cannot_confirm_two_deposits(): void
    {
        $user = User::factory()->create();

        $first = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockFirstAddress',
            'gateway_network' => 'trx',
        ]);
        $first->update(['gateway_uniq_id' => Deposit::gatewayUniqId($first->id)]);
        $first->refresh();

        $second = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockSecondAddress',
            'gateway_network' => 'trx',
        ]);
        $second->update(['gateway_uniq_id' => Deposit::gatewayUniqId($second->id)]);
        $second->refresh();

        $sharedTxid = 'shared-tx-abc';

        $this->postJson('http://coin.test/webhooks/ccapi', $this->signedDepositPayload($first, $sharedTxid, 1))->assertOk();

        $payloadForSecond = $this->signedDepositPayload($second, $sharedTxid, 1);
        $this->postJson('http://coin.test/webhooks/ccapi', $payloadForSecond)->assertOk();

        $first->refresh();
        $second->refresh();

        $this->assertSame(Deposit::STATUS_CONFIRMED, $first->status);
        $this->assertSame(Deposit::STATUS_REJECTED, $second->status);
        $this->assertSame('deposit_duplicate_txid', $second->status_reason);
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

    public function test_ccapi_webhook_rejects_underpaid_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockUnderpay',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'underpay-tx-1', 1, '50.000000');

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_REJECTED, $deposit->status);
        $this->assertSame('50.00000000', number_format((float) $deposit->received_amount, 8, '.', ''));
        $this->assertSame('0.00', number_format((float) $user->wallet->available, 2, '.', ''));

        $log = PaymentWebhookLog::query()->where('deposit_id', $deposit->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Amount mismatch', (string) $log->processing_result);
    }

    public function test_ccapi_webhook_rejects_overpaid_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockOverpay',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'overpay-tx-1', 1, '150.000000');

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_REJECTED, $deposit->status);
        $this->assertSame(PaymentStatusReason::DEPOSIT_AMOUNT_MISMATCH, $deposit->status_reason);
        $this->assertSame('150.00000000', number_format((float) $deposit->received_amount, 8, '.', ''));
        $this->assertSame('0.00', number_format((float) $user->wallet->available, 2, '.', ''));

        $log = PaymentWebhookLog::query()->where('deposit_id', $deposit->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Amount mismatch', (string) $log->processing_result);
    }

    public function test_webhook_rejects_non_ccapi_ip_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config(['coin.payments.ccapi.webhook_ips' => ['168.119.158.209']]);

        $this->postJson('http://coin.test/webhooks/ccapi', [], [
            'REMOTE_ADDR' => '203.0.113.10',
        ])->assertForbidden();
    }

    public function test_ccapi_webhook_rejects_unsigned_payload_even_with_mock_driver(): void
    {
        $payload = [
            'type' => 'in',
            'chain' => 'tron',
            'amount' => '100.000000',
            'confirmation' => 1,
            'label' => 'deposit:1',
        ];

        config(['coin.payments.driver' => 'mock']);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertForbidden();
    }

    public function test_ccapi_webhook_rejects_expired_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TExpiredAddress123',
            'gateway_network' => 'trx',
            'expires_at' => now()->subMinute(),
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'expired-tx-1', 1);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->status);
        $this->assertSame('0.00', number_format((float) $user->wallet->available, 2, '.', ''));

        $log = PaymentWebhookLog::query()->where('deposit_id', $deposit->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('payment window expired', strtolower((string) $log->processing_result));
    }

    public function test_ccapi_webhook_rejects_gateway_reference_mismatch(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TGatewayRef123',
            'gateway_uniq_id' => 'deposit:wrong',
            'gateway_network' => 'trx',
        ]);

        $deposit->update([
            'gateway_uniq_id' => 'deposit:wrong',
        ]);

        $payload = $this->signedDepositPayload($deposit, 'gateway-ref-tx', 1);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);
        $this->assertSame('0.00', number_format((float) $user->fresh()->wallet->available, 2, '.', ''));

        $log = PaymentWebhookLog::query()->where('deposit_id', $deposit->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('gateway reference mismatch', strtolower((string) $log->processing_result));
    }

    public function test_ccapi_webhook_rejects_missing_txid(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TNoTxidAddress',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, '', 1);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);

        $log = PaymentWebhookLog::query()->where('deposit_id', $deposit->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('missing transaction id', strtolower((string) $log->processing_result));
    }

    public function test_ccapi_webhook_rejects_wrong_payment_address(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TExpectedAddress123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = $this->signedDepositPayload($deposit, 'wrong-addr-tx', 1);
        $payload['to'] = 'TWrongAddress999';

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->status);
        $this->assertSame('0.00', number_format((float) $user->wallet->available, 2, '.', ''));

        $log = PaymentWebhookLog::query()->where('deposit_id', $deposit->id)->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Payment address mismatch', (string) $log->processing_result);
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

    public function test_outgoing_ipn_rejects_wrong_payout_amount(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-WRONGAMT1',
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
            'amount' => '10.000000',
            'fee' => '0.000000',
            'txid' => 'withdraw-tx-wrong-amount',
            'pos' => 0,
            'confirmation' => 7,
            'label' => Withdrawal::gatewayUniqId($withdrawal->reference),
            'id' => '999',
        ];

        $payload['sign'] = app(CcapiIpnVerifier::class)->sign($payload, $this->apiKey);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame('withdrawal_ipn_mismatch', $withdrawal->status_reason);
    }

    public function test_outgoing_ipn_rejects_wrong_payout_address(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-WRONGADDR',
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
            'to' => 'TAttackerWallet999',
            'token' => 'USDT',
            'amount' => '25.000000',
            'fee' => '0.000000',
            'txid' => 'withdraw-tx-wrong-address',
            'pos' => 0,
            'confirmation' => 7,
            'label' => Withdrawal::gatewayUniqId($withdrawal->reference),
            'id' => '999',
        ];

        $payload['sign'] = app(CcapiIpnVerifier::class)->sign($payload, $this->apiKey);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame('withdrawal_ipn_mismatch', $withdrawal->status_reason);
    }

    /** @return array<string, mixed> */
    private function signedDepositPayload(
        Deposit $deposit,
        string $txid,
        int $confirmation,
        ?string $amount = null,
    ): array {
        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'in',
            'date' => now()->timestamp,
            'from' => '',
            'to' => $deposit->payment_address,
            'token' => 'USDT',
            'amount' => $amount ?? number_format((float) $deposit->amount, 6, '.', ''),
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

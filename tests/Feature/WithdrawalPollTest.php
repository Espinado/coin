<?php

namespace Tests\Feature;

use App\Models\PaymentWebhookLog;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Payment\WithdrawalPollService;
use App\Services\PlatformSettingsService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WithdrawalPollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'test-ccapi-key',
            'coin.payments.ccapi.poll_stuck_withdrawals' => true,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => true]);
    }

    public function test_poll_marks_confirmed_processing_withdrawal_as_paid(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLL0001',
            'amount' => 25,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'network_label' => 'TRC-20',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
            'sent_at' => now()->subMinutes(5),
        ]);

        Http::fake([
            '*status*' => Http::response([
                'result' => [
                    'id' => '999',
                    'state' => '7',
                    'txid' => 'poll-tx-1',
                    'to' => 'TRecipient123',
                    'amount' => '25.000000',
                ],
            ], 200),
        ]);

        $stats = app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $withdrawal->refresh();

        $this->assertSame(1, $stats['polled']);
        $this->assertSame(1, $stats['completed']);
        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->status);
        $this->assertSame('poll-tx-1', $withdrawal->txid);
        $this->assertNotNull($withdrawal->gateway_poll_checked_at);
        $this->assertStringContainsString('state=7', (string) $withdrawal->gateway_poll_summary);

        $this->assertSame(1, PaymentWebhookLog::query()
            ->where('withdrawal_id', $withdrawal->id)
            ->where('event_type', 'payout_poll')
            ->where('processing_result', 'like', 'processed:%')
            ->count());
    }

    public function test_poll_rejects_confirmed_status_with_mismatched_amount(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLL0002',
            'amount' => 25,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'network_label' => 'TRC-20',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
            'sent_at' => now()->subMinutes(5),
        ]);

        Http::fake([
            '*status*' => Http::response([
                'result' => [
                    'id' => '999',
                    'state' => '7',
                    'txid' => 'poll-tx-wrong',
                    'to' => 'TRecipient123',
                    'amount' => '10.000000',
                ],
            ], 200),
        ]);

        $stats = app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $this->assertSame(1, $stats['polled']);
        $this->assertSame(0, $stats['completed']);

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame('withdrawal_ipn_mismatch', $withdrawal->status_reason);

        $this->assertSame(1, PaymentWebhookLog::query()
            ->where('withdrawal_id', $withdrawal->id)
            ->where('event_type', 'payout_poll')
            ->where('processing_result', 'like', 'processed:%')
            ->count());
    }

    public function test_poll_ignores_confirmed_status_without_amount(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLL0004',
            'amount' => 25,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
        ]);

        Http::fake([
            '*status*' => Http::response([
                'result' => [
                    'id' => '999',
                    'state' => '7',
                    'txid' => 'poll-tx-no-amount',
                ],
            ], 200),
        ]);

        app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $this->assertSame(Withdrawal::STATUS_PROCESSING, $withdrawal->fresh()->status);
    }

    public function test_poll_failed_gateway_status_restores_user_funds(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 450,
            'balance' => 450,
            'pending' => 0,
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLL0005',
            'amount' => 50,
            'base_amount' => 50,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
        ]);

        Http::fake([
            '*status*' => Http::response([
                'result' => [
                    'id' => '999',
                    'state' => '8',
                ],
            ], 200),
        ]);

        app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $withdrawal->refresh();
        $user->refresh();

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame('500.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame('500.00', number_format((float) $user->wallet->balance, 2, '.', ''));
    }

    public function test_poll_skipped_when_mock_driver(): void
    {
        config(['coin.payments.driver' => 'mock']);

        $user = User::factory()->create();
        Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLL0003',
            'amount' => 25,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
        ]);

        $stats = app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $this->assertSame(0, $stats['polled']);
        Http::assertNothingSent();
    }

    public function test_poll_command_runs_successfully(): void
    {
        $this->artisan('coin:poll-stuck-withdrawals')
            ->assertSuccessful();
    }

    public function test_poll_rejects_mock_gateway_reference_on_live_ccapi(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 500,
            'balance' => 1500,
            'pending' => 0,
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLLMOCK',
            'amount' => 1000,
            'base_amount' => 1000,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'gateway_request_id' => 'MOCK-98631',
            'status' => Withdrawal::STATUS_PROCESSING,
            'created_at' => now()->subDays(6),
        ]);

        $stats = app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $withdrawal->refresh();
        $user->wallet->refresh();

        $this->assertSame(1, $stats['polled']);
        $this->assertSame(1, $stats['abandoned']);
        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame('withdrawal_mock_gateway', $withdrawal->status_reason);
        $this->assertSame('1500.00', number_format((float) $user->wallet->available, 2, '.', ''));
        Http::assertNothingSent();
    }

    public function test_poll_rejects_after_repeated_status_errors_and_stale_age(): void
    {
        config([
            'coin.payments.ccapi.withdrawal_poll_stale_hours' => 24,
            'coin.payments.ccapi.withdrawal_poll_error_reject_count' => 30,
        ]);

        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 500,
            'balance' => 1500,
            'pending' => 0,
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-POLLSTALE',
            'amount' => 1000,
            'base_amount' => 1000,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'gateway_request_id' => '3551999',
            'status' => Withdrawal::STATUS_PROCESSING,
            'sent_at' => now()->subDays(2),
            'gateway_poll_summary' => 'poll: Poll error: id_or_label_required',
        ]);

        for ($i = 0; $i < 30; $i++) {
            PaymentWebhookLog::query()->create([
                'gateway' => 'ccapi',
                'event_type' => 'payout_poll',
                'payload' => ['error' => 'id_or_label_required'],
                'signature_valid' => true,
                'idempotency_key' => 'poll:'.$withdrawal->id.':seed-'.$i,
                'withdrawal_id' => $withdrawal->id,
                'processing_result' => 'failed: Poll error: id_or_label_required',
                'processed_at' => now()->subMinutes(30 - $i),
            ]);
        }

        $stats = app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $withdrawal->refresh();

        $this->assertSame(1, $stats['abandoned']);
        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame('withdrawal_poll_stuck', $withdrawal->status_reason);
        Http::assertNothingSent();
    }
}

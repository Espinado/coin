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

    public function test_poll_ignores_confirmed_status_with_mismatched_amount(): void
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
        $this->assertSame(Withdrawal::STATUS_PROCESSING, $withdrawal->fresh()->status);

        $this->assertSame(1, PaymentWebhookLog::query()
            ->where('withdrawal_id', $withdrawal->id)
            ->where('event_type', 'payout_poll')
            ->where('processing_result', 'like', 'ignored:%')
            ->count());
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
}

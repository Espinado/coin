<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Services\Payment\DepositPollService;
use App\Services\PlatformSettingsService;
use App\Support\PaymentStatusReason;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DepositPollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.poll_stuck_deposits' => true,
            'coin.payments.ccapi.deposit_stale_alert_minutes' => 30,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => true]);
    }

    public function test_poll_rejects_expired_pending_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TExpiredAddress',
            'gateway_network' => 'trx',
            'expires_at' => now()->subMinute(),
        ]);

        $stats = app(DepositPollService::class)->pollStuckDeposits();

        $deposit->refresh();

        $this->assertSame(1, $stats['checked']);
        $this->assertSame(1, $stats['expired']);
        $this->assertSame(Deposit::STATUS_REJECTED, $deposit->status);
        $this->assertSame(PaymentStatusReason::DEPOSIT_EXPIRED, $deposit->status_reason);
    }

    public function test_poll_logs_stale_pending_deposit_without_rejecting(): void
    {
        Log::fake();

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 25,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TStaleAddress',
            'gateway_network' => 'trx',
            'expires_at' => now()->addHour(),
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        $stats = app(DepositPollService::class)->pollStuckDeposits();

        $deposit->refresh();

        $this->assertSame(1, $stats['checked']);
        $this->assertSame(0, $stats['expired']);
        $this->assertSame(1, $stats['stale']);
        $this->assertSame(Deposit::STATUS_PENDING, $deposit->status);

        Log::assertLogged('warning', function ($log) use ($deposit) {
            return $log->message === 'deposit.poll.stale_pending'
                && ($log->context['deposit_id'] ?? null) === $deposit->id;
        });
    }

    public function test_poll_skips_when_mock_driver(): void
    {
        config(['coin.payments.driver' => 'mock']);

        $user = User::factory()->create();
        Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TAddress',
            'gateway_network' => 'trx',
            'expires_at' => now()->subMinute(),
        ]);

        $stats = app(DepositPollService::class)->pollStuckDeposits();

        $this->assertSame(0, $stats['checked']);
    }

    public function test_artisan_command_reports_poll_stats(): void
    {
        $user = User::factory()->create();
        Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 15,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TAddress2',
            'gateway_network' => 'trx',
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('coin:poll-stuck-deposits')
            ->expectsOutputToContain('Checked 1 pending deposit(s)')
            ->assertSuccessful();
    }
}

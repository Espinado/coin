<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\WalletHistoryService;
use App\Support\PaymentStatusReason;
use App\Support\PlatformTerms;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_merged_history_includes_pending_deposit_and_rejected_withdrawal_without_duplicating_confirmed_top_up(): void
    {
        $user = User::factory()->create();

        WalletTransaction::query()->create([
            'user_id' => $user->id,
            'occurred_label' => 'Sep 16 · 22:54',
            'type' => PlatformTerms::TX_TOP_UP,
            'source' => 'TOP-13',
            'amount_label' => '+1,000.00 USDT',
            'amount_tone' => 'positive',
            'status_label' => 'COMPLETED',
            'sort_order' => 1,
            'amount' => 1000,
            'currency' => 'USDT',
            'occurred_at' => now()->subDays(8),
        ]);

        $pendingDeposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TAddress',
            'expires_at' => now()->addHour(),
        ]);

        Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_CONFIRMED,
            'method' => 'ccapi',
            'confirmed_at' => now()->subDays(8),
        ]);

        Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-REJECTED1',
            'amount' => 100,
            'base_amount' => 100,
            'currency' => 'USDT',
            'withdrawal_type' => 'usdt',
            'payout_address' => 'TWithdrawAddress',
            'status' => Withdrawal::STATUS_REJECTED,
            'status_reason' => PaymentStatusReason::WITHDRAWAL_GENERIC,
        ]);

        Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-PENDING1',
            'amount' => 50,
            'base_amount' => 50,
            'currency' => 'USDT',
            'withdrawal_type' => 'usdt',
            'payout_address' => 'TWithdrawAddress2',
            'status' => Withdrawal::STATUS_PENDING,
        ]);

        $entries = app(WalletHistoryService::class)->paginate($user->id, perPage: 20)->items();

        $this->assertCount(4, $entries);

        $kinds = collect($entries)->map(fn ($entry) => $entry->kind.':'.$entry->sourceLabel)->all();

        $this->assertContains('transaction:TOP-13', $kinds);
        $this->assertContains('deposit:'.$pendingDeposit->publicReference(), $kinds);
        $this->assertContains('withdrawal:WD-REJECTED1', $kinds);
        $this->assertContains('withdrawal:WD-PENDING1', $kinds);
        $this->assertFalse(collect($kinds)->contains(fn (string $kind) => str_starts_with($kind, 'deposit:TOP-') && $kind !== 'deposit:'.$pendingDeposit->publicReference()));
    }
}

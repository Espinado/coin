<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Withdrawal;
use App\Services\PlatformSettingsService;
use App\Services\WithdrawalService;
use App\Support\PlatformTerms;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PayoutAddressTest;
use Tests\TestCase;

class WithdrawalPlatformFeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlatformSettingsSeeder::class);
        app(PlatformSettingsService::class)->setMany(['network_fee' => '1.00']);
    }

    public function test_withdrawal_reserves_payout_plus_platform_fee(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 100, 'USDT');

        $user->wallet->refresh();

        $this->assertSame(100.0, (float) $withdrawal->amount);
        $this->assertSame(1.0, (float) $withdrawal->platform_fee);
        $this->assertSame(99.0, (float) $user->wallet->available);
        $this->assertSame(101.0, (float) $user->wallet->pending);
    }

    public function test_withdrawal_rejected_when_available_covers_payout_but_not_fee(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 100.5,
            'balance' => 100.5,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.insufficient_funds'));

        app(WithdrawalService::class)->createForUser($user, 100, 'USDT');
    }

    public function test_mark_paid_records_payout_and_platform_fee_transactions(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 0,
            'balance' => 101,
            'pending' => 101,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-FEE001',
            'amount' => 100,
            'base_amount' => 100,
            'platform_fee' => 1,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
            'status' => Withdrawal::STATUS_PROCESSING,
        ]);

        app(WithdrawalService::class)->markPaidFromGateway($withdrawal, 'tx-fee-1', '7');

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => PlatformTerms::TX_PAYOUT,
            'source' => 'WD-FEE001',
            'amount_label' => '-100.00 USDT',
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => PlatformTerms::TX_PLATFORM_FEE,
            'source' => 'WD-FEE001:fee',
            'amount_label' => '-1.00 USDT',
        ]);
    }
}

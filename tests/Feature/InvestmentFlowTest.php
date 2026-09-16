<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\ReferralProfile;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DepositService;
use App\Services\PlanPurchaseService;
use App\Services\ProfitAccrualService;
use App\Services\ReferralService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.deposits.auto_confirm_mock' => true,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(PlanSeeder::class);
    }

    public function test_mock_deposit_plan_purchase_referral_commission_and_daily_accrual(): void
    {
        $referrer = User::factory()->create();
        ReferralProfile::query()->create([
            'user_id' => $referrer->id,
            'code' => 'COIN-REF99',
            'level1_percent' => 20,
            'level2_percent' => 0,
        ]);

        $buyer = User::factory()->create([
            'referred_by_user_id' => $referrer->id,
        ]);

        app(ReferralService::class)->ensureReferralProfile($buyer);

        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($buyer, 2000);

        $buyer->refresh();
        $this->assertSame('2000.00', number_format((float) $buyer->wallet->available, 2, '.', ''));

        $contract = app(PlanPurchaseService::class)->purchase($buyer, $core, 1100);

        $this->assertSame('active', $contract->status);
        $this->assertSame('1100.00', number_format((float) $contract->principal_amount, 2, '.', ''));

        $buyer->refresh();
        $referrer->refresh();

        $this->assertSame('900.00', number_format((float) $buyer->wallet->available, 2, '.', ''));
        $this->assertSame('1100.00', number_format((float) $buyer->wallet->locked_balance, 2, '.', ''));
        $this->assertSame('220.00', number_format((float) $referrer->wallet->available, 2, '.', ''));

        $result = app(ProfitAccrualService::class)->accrueDaily();

        $this->assertSame(1, $result['contracts_processed']);
        $this->assertGreaterThan(0, $result['total_profit']);

        $buyer->refresh();
        $this->assertGreaterThan(900, (float) $buyer->wallet->available);

        $repeat = app(ProfitAccrualService::class)->accrueDaily();
        $this->assertSame(0, $repeat['contracts_processed']);
    }

    public function test_mature_contract_releases_principal_to_available_balance(): void
    {
        $buyer = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($buyer, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($buyer, $core, 1100);

        $buyer->refresh();
        $this->assertSame('900.00', number_format((float) $buyer->wallet->available, 2, '.', ''));
        $this->assertSame('1100.00', number_format((float) $buyer->wallet->locked_balance, 2, '.', ''));

        $contract->update(['ends_at' => now()->subMinute()]);

        $matured = app(ProfitAccrualService::class)->settleMatureContractsForUser($buyer);

        $this->assertSame(1, $matured);

        $buyer->refresh();
        $contract->refresh();

        $this->assertSame(Contract::STATUS_COMPLETED, $contract->status);
        $this->assertSame('0.00', number_format((float) $buyer->wallet->locked_balance, 2, '.', ''));
        $this->assertSame('2000.00', number_format((float) $buyer->wallet->available, 2, '.', ''));
    }

    public function test_btc_deposit_is_converted_to_usdt_balance(): void
    {
        $buyer = User::factory()->create();

        app(DepositService::class)->createPending($buyer, 4, 'BTC');

        $buyer->refresh();

        $this->assertSame('USDT', $buyer->wallet->currency);
        $this->assertSame('2.00', number_format((float) $buyer->wallet->available, 2, '.', ''));

        $deposit = $buyer->deposits()->firstOrFail();
        $this->assertSame('4.00', number_format((float) $deposit->amount, 2, '.', ''));
        $this->assertSame('BTC', $deposit->currency);
        $this->assertSame('2.00', number_format((float) $deposit->credited_amount, 2, '.', ''));
        $this->assertSame('USDT', $deposit->credited_currency);
        $this->assertSame('2.00000000', number_format((float) $deposit->exchange_rate, 8, '.', ''));
    }
}

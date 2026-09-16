<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\ReferralCommission;
use App\Models\ReferralProfile;
use App\Models\User;
use App\Services\DepositService;
use App\Services\PlanPurchaseService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanChangeTest extends TestCase
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

    public function test_upgrade_changes_plan_increases_principal_and_preserves_start_date(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($user, 5000);

        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);
        $startedAt = $contract->started_at?->copy();
        $daysElapsed = $contract->days_elapsed;

        $updated = app(PlanPurchaseService::class)->changePlan($user->fresh(), $contract->fresh(['plan']), $cluster);

        $user->refresh();

        $this->assertSame($cluster->id, $updated->plan_id);
        $this->assertSame('3400.00', number_format((float) $updated->principal_amount, 2, '.', ''));
        $this->assertSame('1800.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame('3400.00', number_format((float) $user->wallet->locked_balance, 2, '.', ''));
        $this->assertTrue($updated->started_at?->eq($startedAt));
        $this->assertSame($daysElapsed, $updated->days_elapsed);
        $this->assertSame((float) $cluster->annual_profit_percent, (float) $updated->annual_profit_percent);
        $this->assertSame($cluster->duration_days, $updated->duration_days);
        $this->assertTrue($updated->ends_at?->eq($startedAt?->copy()->addDays($cluster->duration_days)));
    }

    public function test_upgrade_fails_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($user, 1200);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $this->expectException(\RuntimeException::class);

        app(PlanPurchaseService::class)->changePlan($user->fresh(), $contract->fresh(['plan']), $cluster);
    }

    public function test_downgrade_keeps_principal_and_updates_plan_terms(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $node = Plan::query()->where('slug', 'node')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);
        $startedAt = $contract->started_at?->copy();

        $updated = app(PlanPurchaseService::class)->changePlan($user->fresh(), $contract->fresh(['plan']), $node);

        $user->refresh();

        $this->assertSame($node->id, $updated->plan_id);
        $this->assertSame('1100.00', number_format((float) $updated->principal_amount, 2, '.', ''));
        $this->assertSame('900.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame('1100.00', number_format((float) $user->wallet->locked_balance, 2, '.', ''));
        $this->assertTrue($updated->started_at?->eq($startedAt));
        $this->assertSame((float) $node->annual_profit_percent, (float) $updated->annual_profit_percent);
        $this->assertSame($node->duration_days, $updated->duration_days);
    }

    public function test_upgrade_pays_referrer_twenty_percent_of_top_up_difference(): void
    {
        $referrer = User::factory()->create();
        ReferralProfile::query()->create([
            'user_id' => $referrer->id,
            'code' => 'COIN-REFUP',
            'level1_percent' => 20,
            'level2_percent' => 0,
        ]);

        $buyer = User::factory()->create([
            'referred_by_user_id' => $referrer->id,
        ]);

        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($buyer, 5000);

        $contract = app(PlanPurchaseService::class)->purchase($buyer, $core, 1100);

        $referrer->refresh();
        $this->assertSame('220.00', number_format((float) $referrer->wallet->available, 2, '.', ''));

        app(PlanPurchaseService::class)->changePlan($buyer->fresh(), $contract->fresh(['plan']), $cluster);

        $referrer->refresh();

        $this->assertSame('680.00', number_format((float) $referrer->wallet->available, 2, '.', ''));

        $commission = ReferralCommission::query()->where('contract_id', $contract->id)->firstOrFail();

        $this->assertSame('3400.00', number_format((float) $commission->purchase_amount, 2, '.', ''));
        $this->assertSame('680.00', number_format((float) $commission->commission_amount, 2, '.', ''));
    }

    public function test_cannot_change_to_same_plan(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $this->expectException(\RuntimeException::class);

        app(PlanPurchaseService::class)->changePlan($user->fresh(), $contract->fresh(['plan']), $core);
    }
}

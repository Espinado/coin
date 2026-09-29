<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contract;
use App\Models\EarlyUnlockRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\DepositService;
use App\Services\EarlyUnlockRequestService;
use App\Services\PlanPurchaseService;
use App\Services\PlatformSettingsService;
use App\Support\PlatformTerms;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarlyUnlockRequestTest extends TestCase
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

    public function test_fee_formula_uses_percent_with_minimum_floor(): void
    {
        $settings = app(PlatformSettingsService::class);

        $this->assertSame(300.0, $settings->calculateEarlyUnlockFeeAmount(1000));
        $this->assertSame(50.0, $settings->calculateEarlyUnlockFeeAmount(100));
        $this->assertSame(50.0, $settings->calculateEarlyUnlockFeeAmount(50));
        $this->assertSame(40.0, $settings->calculateEarlyUnlockFeeAmount(40));
    }

    public function test_user_can_create_pending_early_unlock_request(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $request = app(EarlyUnlockRequestService::class)->createRequest($user->fresh(), $contract->fresh());

        $this->assertSame(EarlyUnlockRequest::STATUS_PENDING, $request->status);
        $this->assertSame('1100.00', number_format((float) $request->principal_amount, 2, '.', ''));
        $this->assertSame('330.00', number_format((float) $request->fee_amount, 2, '.', ''));
        $this->assertSame('770.00', number_format((float) $request->credit_amount, 2, '.', ''));
        $this->assertTrue($contract->fresh()->isActive());
    }

    public function test_admin_approval_credits_net_and_closes_contract(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Early Unlock Admin',
            'email' => 'early-admin@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);
        $availableBefore = (float) $user->fresh()->wallet->available;
        $lockedBefore = (float) $user->fresh()->wallet->locked_balance;

        $request = app(EarlyUnlockRequestService::class)->createRequest($user->fresh(), $contract->fresh());
        app(EarlyUnlockRequestService::class)->approve($request->fresh(), $admin);

        $user->refresh();
        $contract->refresh();
        $request->refresh();

        $this->assertSame(EarlyUnlockRequest::STATUS_APPROVED, $request->status);
        $this->assertSame(Contract::STATUS_EARLY_CLOSED, $contract->status);
        $this->assertSame(
            number_format($availableBefore + 770, 2, '.', ''),
            number_format((float) $user->wallet->available, 2, '.', '')
        );
        $this->assertSame(
            number_format($lockedBefore - 1100, 2, '.', ''),
            number_format((float) $user->wallet->locked_balance, 2, '.', '')
        );
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => PlatformTerms::TX_EARLY_UNLOCK,
            'amount' => 770,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => PlatformTerms::TX_PLATFORM_FEE,
            'amount' => -330,
        ]);
        $this->assertDatabaseHas('platform_commissions', [
            'user_id' => $user->id,
            'kind' => 'early_unlock',
            'amount' => 330,
            'reference' => $request->reference,
        ]);
        $this->assertFalse(
            $user->contracts()->where('status', Contract::STATUS_ACTIVE)->whereKey($contract->id)->exists()
        );
    }

    public function test_admin_reject_leaves_contract_active(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Reject Admin',
            'email' => 'early-reject@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);
        $request = app(EarlyUnlockRequestService::class)->createRequest($user->fresh(), $contract->fresh());

        app(EarlyUnlockRequestService::class)->reject($request->fresh(), $admin, 'Not eligible');

        $this->assertSame(EarlyUnlockRequest::STATUS_REJECTED, $request->fresh()->status);
        $this->assertTrue($contract->fresh()->isActive());
    }

    public function test_cannot_create_second_pending_request(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);
        app(EarlyUnlockRequestService::class)->createRequest($user->fresh(), $contract->fresh());

        $this->expectException(\RuntimeException::class);
        app(EarlyUnlockRequestService::class)->createRequest($user->fresh(), $contract->fresh());
    }
}

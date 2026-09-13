<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Plan;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\EpochService;
use App\Services\PlatformSettingsService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CoinDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(AdminSeeder::class);
        $this->seed(CoinDemoSeeder::class);

        $this->admin = Admin::query()->firstOrFail();
    }

    public function test_admin_users_index_and_update(): void
    {
        $user = User::query()->where('email', 'test@test.lv')->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/users')
            ->assertOk()
            ->assertSee('test@test.lv');

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/users/'.$user->id, [
                'kyc_status' => User::KYC_PENDING,
                'is_blocked' => true,
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_blocked);
        $this->assertSame(User::KYC_PENDING, $user->kyc_status);
    }

    public function test_admin_withdrawal_status_update(): void
    {
        $withdrawal = Withdrawal::query()->where('reference', 'WD-DEMO120')->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/withdrawals/'.$withdrawal->id.'/status', [
                'status' => Withdrawal::STATUS_APPROVED,
                'admin_note' => 'Approved for payout batch.',
            ])
            ->assertRedirect();

        $withdrawal->refresh();
        $this->assertSame(Withdrawal::STATUS_APPROVED, $withdrawal->status);
    }

    public function test_admin_plan_crud(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('http://admin.coin.test/plans', [
                'name' => 'Pilot',
                'slug' => 'pilot',
                'tier_label' => 'TEST',
                'price_label' => '$99',
                'tflops' => 100,
                'duration_days' => 30,
                'infra' => 'Shared pool',
                'reward_multiplier' => 0.75,
                'daily_estimate' => 0.5,
                'max_tflops' => 200,
                'sort_order' => 10,
                'capacity_percent' => 5,
                'is_active' => true,
                'is_featured' => false,
            ])
            ->assertRedirect();

        $plan = Plan::query()->where('slug', 'pilot')->first();
        $this->assertNotNull($plan);

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/plans/'.$plan->id, [
                'name' => 'Pilot Plus',
                'slug' => 'pilot',
                'tier_label' => 'TEST',
                'price_label' => '$129',
                'tflops' => 120,
                'duration_days' => 30,
                'infra' => 'Shared pool',
                'reward_multiplier' => 0.80,
                'daily_estimate' => 0.6,
                'max_tflops' => 220,
                'sort_order' => 10,
                'capacity_percent' => 8,
                'is_active' => true,
                'is_featured' => true,
            ])
            ->assertRedirect();

        $this->assertSame('Pilot Plus', $plan->fresh()->name);
    }

    public function test_epoch_settlement_and_settings(): void
    {
        app(PlatformSettingsService::class)->setMany([
            'reward_rate' => '0.0042',
            'epochs_per_day' => '3',
        ]);

        $epoch = app(EpochService::class)->runSettlement($this->admin);

        $this->assertSame(1, $epoch->number);
        $this->assertGreaterThan(0, (float) $epoch->total_rewards);

        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/epochs')
            ->assertOk()
            ->assertSee('#1');

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/settings', [
                'reward_rate' => '0.0050',
                'epochs_per_day' => '4',
                'token_symbol' => 'COIN',
                'min_withdrawal' => '15',
                'network_fee' => '0.50',
                'withdrawal_processing_hours' => '24',
                'referral_level1_percent' => '5',
                'referral_level2_percent' => '2',
                'kyc_required_for_withdrawal' => false,
                'maintenance_mode' => false,
            ])
            ->assertRedirect();

        $this->assertSame(0.005, app(PlatformSettingsService::class)->rewardRate());
    }

    public function test_admin_overview_dashboard(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/dashboard')
            ->assertOk()
            ->assertSee('PLATFORM OVERVIEW')
            ->assertSee('PENDING WITHDRAWALS');
    }
}

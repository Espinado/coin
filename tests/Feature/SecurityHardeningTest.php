<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\WithdrawalService;
use App\Support\AdminRole;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(AdminSeeder::class);
        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_mock_deposit_does_not_auto_confirm_in_production(): void
    {
        config(['coin.deposits.auto_confirm_mock' => true]);
        app()->detectEnvironment(fn () => 'production');

        $user = User::factory()->create();

        $deposit = app(DepositService::class)->createPending($user, 100);

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);
    }

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('http://coin.test/dashboard')
            ->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_blocked_user_is_logged_out_from_dashboard(): void
    {
        $user = User::factory()->create(['is_blocked' => false]);

        $this->actingAs($user)
            ->get('http://coin.test/dashboard')
            ->assertOk();

        $user->update(['is_blocked' => true]);

        $this->actingAs($user->fresh())
            ->get('http://coin.test/dashboard')
            ->assertRedirect(route('login', absolute: false));
    }

    public function test_enable_email_two_factor_requires_password(): void
    {
        $user = User::factory()->create(['password' => 'SecretPass1!']);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('enableEmailTwoFactor')
            ->assertHasErrors(['profileTwoFactorPassword']);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileTwoFactorPassword', 'SecretPass1!')
            ->call('enableEmailTwoFactor')
            ->assertHasNoErrors();

        $this->assertTrue($user->fresh()->hasEmailTwoFactorEnabled());
    }

    public function test_rejected_withdrawal_cannot_be_marked_paid(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();

        config(['coin.deposits.auto_confirm_mock' => true]);
        app()->detectEnvironment(fn () => 'testing');

        app(DepositService::class)->createPending($user, 200);
        $withdrawal = app(WithdrawalService::class)->createForUser($user, 50);

        app(WithdrawalService::class)->updateStatus($withdrawal, Withdrawal::STATUS_REJECTED, $admin);

        $this->expectException(\RuntimeException::class);

        app(WithdrawalService::class)->updateStatus($withdrawal->fresh(), Withdrawal::STATUS_PAID, $admin);
    }

    public function test_viewer_admin_cannot_update_platform_settings(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer',
            'email' => 'viewer@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $this->actingAs($viewer, 'admin')
            ->patch('http://admin.coin.test/settings', [
                'token_symbol' => 'USDT',
                'min_withdrawal' => '10',
                'network_fee' => '0.50',
                'withdrawal_processing_hours' => '24',
                'referral_level1_percent' => '20',
                'referral_level2_percent' => '0',
                'kyc_required_for_withdrawal' => false,
                'maintenance_mode' => false,
                'btc_per_usdt' => '2',
                'profit_accrual_time' => '09:00',
            ])
            ->assertForbidden();
    }

    public function test_existing_admins_are_superadmin_after_migration(): void
    {
        $admin = Admin::query()->firstOrFail();

        $this->assertSame(AdminRole::Superadmin, $admin->adminRole());
    }
}

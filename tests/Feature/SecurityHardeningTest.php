<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Admin;
use App\Models\Deposit;
use App\Models\Plan;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\Payment\PaymentSimulatorService;
use App\Services\PlanPurchaseService;
use App\Services\PlatformSettingsService;
use App\Services\WithdrawalService;
use App\Support\AdminRole;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
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

    public function test_admin_cannot_manually_confirm_deposit_via_removed_route(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TAdminBlockTest',
            'gateway_network' => 'trx',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("http://admin.coin.test/deposits/{$deposit->id}/confirm")
            ->assertNotFound();
    }

    public function test_deposit_service_rejects_admin_confirm(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'mock',
        ]);

        $this->expectException(\RuntimeException::class);

        app(DepositService::class)->confirm($deposit, $admin);
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
        $user->wallet->update([
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);
        $withdrawal = app(WithdrawalService::class)->createForUser($user, 50);

        app(WithdrawalService::class)->updateStatus($withdrawal, Withdrawal::STATUS_REJECTED, $admin);

        $this->expectException(\RuntimeException::class);

        app(WithdrawalService::class)->updateStatus($withdrawal->fresh(), Withdrawal::STATUS_PAID, $admin);
    }

    public function test_viewer_admin_cannot_access_finance_routes(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer',
            'email' => 'viewer@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/deposits')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/payment-logs')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/withdrawals')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/commissions')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/profit-accrual')
            ->assertForbidden();
    }

    public function test_viewer_admin_cannot_access_sensitive_admin_sections(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer',
            'email' => 'viewer-sections@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/users')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/settings')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/admins')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/legal')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/plans')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/plan-changes')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/broadcasts')
            ->assertForbidden();

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/support')
            ->assertForbidden();
    }

    public function test_viewer_admin_cannot_approve_withdrawal(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer',
            'email' => 'viewer-withdraw@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'amount' => 25,
            'currency' => 'USDT',
            'status' => Withdrawal::STATUS_PENDING,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
            'reference' => 'WD-TEST-1',
        ]);

        $this->actingAs($viewer, 'admin')
            ->post("http://admin.coin.test/withdrawals/{$withdrawal->id}/approve")
            ->assertForbidden();
    }

    public function test_viewer_admin_can_access_dashboard(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer',
            'email' => 'viewer-dashboard@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/dashboard')
            ->assertOk();
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
                'min_deposit' => '10.00',
                'min_withdrawal' => '10',
                'network_fee' => '0.50',
                'withdrawal_processing_hours' => '24',
                'referral_level1_percent' => '20',
                'referral_level2_percent' => '0',
                'kyc_required_for_withdrawal' => false,
                'maintenance_mode' => false,
                'usdt_per_btc' => '80000',
                'profit_accrual_time' => '09:00',
            ])
            ->assertForbidden();
    }

    public function test_existing_admins_are_superadmin_after_migration(): void
    {
        $admin = Admin::query()->firstOrFail();

        $this->assertSame(AdminRole::Superadmin, $admin->adminRole());
    }

    public function test_deposit_simulation_is_blocked_in_production_for_ccapi_deposits(): void
    {
        config(['coin.payments.driver' => 'ccapi']);
        app()->detectEnvironment(fn () => 'production');

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TBlockedSim123',
            'gateway_network' => 'trx',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.payment_simulation_blocked'));

        app(PaymentSimulatorService::class)->simulateDepositIpn($deposit);
    }

    public function test_second_withdrawal_rejected_when_balance_already_reserved(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 150,
            'balance' => 150,
            'pending' => 0,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        app(WithdrawalService::class)->createForUser($user, 100);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.insufficient_funds'));

        app(WithdrawalService::class)->createForUser($user, 100);
    }

    public function test_admin_cannot_disable_payment_gate_in_production_with_ccapi_driver(): void
    {
        config(['coin.payments.driver' => 'ccapi']);
        app()->detectEnvironment(fn () => 'production');

        $admin = Admin::query()->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->from('http://admin.coin.test/settings')
            ->patch('http://admin.coin.test/settings', [
                'token_symbol' => 'USDT',
                'min_deposit' => '10.00',
                'min_withdrawal' => '10',
                'network_fee' => '0.50',
                'withdrawal_processing_hours' => '24',
                'referral_level1_percent' => '20',
                'referral_level2_percent' => '0',
                'kyc_required_for_withdrawal' => false,
                'maintenance_mode' => false,
                'payment_gate_enabled' => false,
                'profit_accrual_time' => '09:00',
            ])
            ->assertSessionHasErrors('payment_gate_enabled');

        $this->assertTrue(app(PlatformSettingsService::class)->paymentGateEnabled());
    }

    public function test_save_profile_email_requires_password(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@coin.test',
            'password' => 'SecretPass1!',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileEmail', 'newowner@coin.test')
            ->call('saveProfileEmail')
            ->assertHasErrors(['profileEmailPassword']);

        $this->assertSame('owner@coin.test', $user->fresh()->email);
    }

    public function test_livewire_dashboard_ignores_tampered_user_model(): void
    {
        $this->seed(PlanSeeder::class);

        $actor = User::factory()->create([
            'email' => 'actor@coin.test',
            'password' => 'SecretPass1!',
        ]);
        $victim = User::factory()->create(['email' => 'victim@coin.test']);

        Livewire::actingAs($actor)
            ->test(Dashboard::class)
            ->set('user', $victim)
            ->set('profileEmail', 'hacked@coin.test')
            ->set('profileEmailPassword', 'SecretPass1!')
            ->call('saveProfileEmail')
            ->assertHasNoErrors();

        $this->assertSame('victim@coin.test', $victim->fresh()->email);
        $this->assertSame('hacked@coin.test', $actor->fresh()->email);
        $this->assertNull($actor->fresh()->email_verified_at);
    }

    public function test_plan_purchase_rejects_amount_above_calculator_max(): void
    {
        $this->seed(PlanSeeder::class);

        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 100_000,
            'balance' => 100_000,
        ]);

        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $tooMuch = (float) ($core->calculatorMaxAmount() + 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Amount exceeds the maximum investment for this plan.');

        app(PlanPurchaseService::class)->purchase($user, $core, $tooMuch);
    }

    public function test_viewer_admin_dashboard_hides_financial_metrics(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer Metrics',
            'email' => 'viewer-metrics@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/dashboard')
            ->assertOk()
            ->assertDontSee(__('coin.admin.locked_principal'), false);
    }

    public function test_registration_is_rate_limited(): void
    {
        RateLimiter::clear('127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('http://coin.test/register', [
                'name' => 'User '.$i,
                'email' => "user{$i}@coin.test",
                'phone' => '+3712000000'.$i,
                'password' => 'SecretPass1!',
                'password_confirmation' => 'SecretPass1!',
            ])->assertRedirect();
        }

        $this->post('http://coin.test/register', [
            'name' => 'Blocked User',
            'email' => 'blocked@coin.test',
            'phone' => '+37120000099',
            'password' => 'SecretPass1!',
            'password_confirmation' => 'SecretPass1!',
        ])->assertStatus(429);
    }

    public function test_payment_security_monitor_reads_configured_thresholds(): void
    {
        config([
            'coin.payments.ccapi.security_monitor.lookback_minutes' => 30,
            'coin.payments.ccapi.security_monitor.invalid_signature_threshold' => 2,
        ]);

        $this->artisan('coin:monitor-payment-security')
            ->assertSuccessful();
    }
}

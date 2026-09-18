<?php

namespace Tests\Feature;

use App\Events\WithdrawalUpdated;
use App\Mail\UserEventNotificationMail;
use App\Models\Admin;
use App\Models\Plan;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Event;
use App\Services\DepositService;
use App\Services\PlanPurchaseService;
use App\Services\PlatformSettingsService;
use App\Services\ProfitAccrualService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CoinDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

        config(['coin.deposits.auto_confirm_mock' => true]);

        $this->seed(AdminSeeder::class);
        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(PlanSeeder::class);
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
        Event::fake([WithdrawalUpdated::class]);

        $user = User::query()->where('email', 'test@test.lv')->firstOrFail();

        app(DepositService::class)->createPending($user, 200);
        $user->refresh();

        $withdrawal = app(\App\Services\WithdrawalService::class)->createForUser($user, 50);

        Event::assertDispatched(WithdrawalUpdated::class, function (WithdrawalUpdated $event) use ($withdrawal): bool {
            return $event->withdrawal->is($withdrawal);
        });

        $user->refresh();
        $wallet = $user->wallet;
        $balanceBeforeApproval = (float) $wallet->balance;

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/withdrawals/'.$withdrawal->id.'/status', [
                'status' => Withdrawal::STATUS_PAID,
                'admin_note' => 'Sent on-chain.',
            ])
            ->assertRedirect();

        $withdrawal->refresh();
        $wallet->refresh();
        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->status);
        $this->assertSame($balanceBeforeApproval - 50, (float) $wallet->balance);
        $this->assertSame(0.0, (float) $wallet->pending);

        Event::assertDispatched(WithdrawalUpdated::class, function (WithdrawalUpdated $event) use ($withdrawal): bool {
            return $event->withdrawal->is($withdrawal)
                && $event->withdrawal->status === Withdrawal::STATUS_PAID;
        });
    }

    public function test_admin_marks_withdrawal_paid_and_emails_user(): void
    {
        Mail::fake();

        $user = User::query()->where('email', 'test@test.lv')->firstOrFail();

        app(DepositService::class)->createPending($user, 200);
        $user->refresh();

        $withdrawal = app(\App\Services\WithdrawalService::class)->createForUser($user, 50);

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/withdrawals/'.$withdrawal->id.'/status', [
                'status' => Withdrawal::STATUS_PAID,
                'admin_note' => 'Sent on-chain.',
            ])
            ->assertRedirect();

        Mail::assertSent(UserEventNotificationMail::class, function (UserEventNotificationMail $mail) use ($user): bool {
            return $mail->hasTo($user->email)
                && $mail->subjectLine === __('coin.notifications.mail.payout_subject');
        });
    }

    public function test_admin_cannot_change_closed_withdrawal_status(): void
    {
        $user = User::query()->where('email', 'test@test.lv')->firstOrFail();

        app(DepositService::class)->createPending($user, 200);
        $user->refresh();

        $withdrawal = app(\App\Services\WithdrawalService::class)->createForUser($user, 50);

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/withdrawals/'.$withdrawal->id.'/status', [
                'status' => Withdrawal::STATUS_PAID,
            ])
            ->assertRedirect();

        $response = $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/withdrawals/'.$withdrawal->id.'/status', [
                'status' => Withdrawal::STATUS_REJECTED,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', __('coin.admin.withdrawal_closed'));
        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->fresh()->status);
    }

    public function test_admin_plan_crud(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('http://admin.coin.test/plans', [
                'name' => 'Pilot',
                'slug' => 'pilot',
                'tier_label' => 'TEST',
                'price_label' => '$99',
                'min_deposit' => 99,
                'price_amount' => 99,
                'annual_profit_percent' => 10,
                'currency' => 'USDT',
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
                'min_deposit' => 129,
                'price_amount' => 129,
                'annual_profit_percent' => 11,
                'currency' => 'USDT',
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

    public function test_profit_accrual_and_settings(): void
    {
        $user = User::query()->where('email', 'test@test.lv')->firstOrFail();
        $plan = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($user, 2000);
        app(PlanPurchaseService::class)->purchase($user->fresh(), $plan, 1100);

        $before = WalletTransaction::query()->where('type', 'Daily profit')->count();

        $result = app(ProfitAccrualService::class)->accrueDaily();

        $this->assertGreaterThan(0, $result['contracts_processed']);
        $this->assertGreaterThan($before, WalletTransaction::query()->where('type', 'Daily profit')->count());

        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/profit-accrual')
            ->assertOk()
            ->assertSee('Profit accrual')
            ->assertSee('Daily profit');

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/settings', [
                'token_symbol' => 'USDT',
                'min_withdrawal' => '15',
                'network_fee' => '0.50',
                'withdrawal_processing_hours' => '24',
                'referral_level1_percent' => '20',
                'referral_level2_percent' => '0',
                'kyc_required_for_withdrawal' => false,
                'maintenance_mode' => false,
                'btc_per_usdt' => '2',
                'profit_accrual_time' => '10:30',
            ])
            ->assertRedirect();

        $settings = app(PlatformSettingsService::class);

        $this->assertSame(20, $settings->getInt('referral_level1_percent'));
        $this->assertSame('10:30', $settings->profitAccrualTime());
    }

    public function test_admin_can_save_legal_company_info(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/settings')
            ->assertOk()
            ->assertSee('Юридическая информация');

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/settings/legal', [
                'company_name' => 'CloudFlops SIA',
                'company_legal_address' => 'Rīga, Brīvības iela 1',
                'company_physical_address' => 'Rīga, Brīvības iela 1',
                'company_registration_number' => '40103123456',
                'company_license_number' => 'LV-12345',
                'company_phone' => '+371 20000000',
                'company_email' => 'legal@cloudflops.example',
            ])
            ->assertRedirect();

        $legal = app(PlatformSettingsService::class)->legalInfo();

        $this->assertSame('CloudFlops SIA', $legal['company_name']);
        $this->assertSame('legal@cloudflops.example', $legal['company_email']);
    }

    public function test_admin_overview_dashboard(): void
    {
        $this->assertSame(1, User::query()->count());

        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/dashboard')
            ->assertOk()
            ->assertSee('PLATFORM OVERVIEW')
            ->assertSee('PENDING PAYOUTS')
            ->assertSee('LOCKED PRINCIPAL');
    }
}

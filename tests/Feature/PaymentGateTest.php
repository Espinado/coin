<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Services\DepositService;
use App\Services\Payment\MockPaymentGateway;
use App\Services\PlatformSettingsService;
use App\Services\WithdrawalService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PayoutAddressTest;
use Tests\TestCase;

class PaymentGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
            'coin.payments.driver' => 'mock',
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(AdminSeeder::class);
        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_deposit_via_gateway_is_blocked_when_payment_gate_disabled(): void
    {
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => false]);

        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.payment_gate_disabled'));

        app(DepositService::class)->initiateWithGateway(
            $user,
            100,
            'USDT',
            app(MockPaymentGateway::class),
        );
    }

    public function test_withdrawal_is_blocked_when_payment_gate_disabled(): void
    {
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => false]);

        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 500,
            'balance' => 500,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.payment_gate_disabled'));

        app(WithdrawalService::class)->createForUser($user, 100);
    }

    public function test_admin_can_enable_payment_gate_in_settings(): void
    {
        $admin = Admin::query()->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->patch('http://admin.coin.test/settings', [
                'token_symbol' => 'USDT',
                'min_deposit' => '10.00',
                'min_withdrawal' => '10.00',
                'network_fee' => '0.50',
                'withdrawal_processing_hours' => '24',
                'referral_level1_percent' => '20',
                'referral_level2_percent' => '0',
                'kyc_required_for_withdrawal' => false,
                'payment_gate_enabled' => true,
                'maintenance_mode' => false,
                'profit_accrual_time' => '09:00',
            ])
            ->assertRedirect();

        $this->assertTrue(app(PlatformSettingsService::class)->paymentGateEnabled());
    }

    public function test_live_gateway_is_selected_when_gate_enabled_and_ccapi_driver_configured(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'test-key',
        ]);

        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => true]);

        $this->assertTrue(app(PlatformSettingsService::class)->usesLivePaymentGateway());
        $this->assertInstanceOf(
            \App\Services\Payment\CryptoCurrencyApiGateway::class,
            app(\App\Services\Payment\PaymentGatewayInterface::class),
        );
    }
}

<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Deposit;
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

    public function test_deposit_works_in_mock_driver_mode(): void
    {
        $user = User::factory()->create();

        $deposit = app(DepositService::class)->initiateWithGateway(
            $user,
            100,
            'USDT',
            app(MockPaymentGateway::class),
        );

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->status);
        $this->assertStringStartsWith('MOCK-', $deposit->payment_address);
    }

    public function test_withdrawal_works_in_mock_driver_mode(): void
    {
        $user = User::factory()->create();
        $wallet = app(\App\Services\WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 500,
            'balance' => 500,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);
        $user->unsetRelation('wallet');

        $withdrawal = app(WithdrawalService::class)->createForUser($user->fresh(), 100);

        $this->assertSame('100.00', number_format((float) $withdrawal->amount, 2, '.', ''));
    }

    public function test_live_deposit_requires_ccapi_api_key(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => '',
        ]);

        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.payment_gate_ccapi_missing'));

        app(DepositService::class)->initiateWithGateway(
            $user,
            100,
            'USDT',
            app(MockPaymentGateway::class),
        );
    }

    public function test_admin_settings_no_longer_expose_payment_gate_toggle(): void
    {
        $admin = Admin::query()->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/settings')
            ->assertOk()
            ->assertDontSee(__('coin.settings.payment_gate'), false);

        $this->assertArrayNotHasKey(
            'payment_gate_enabled',
            app(PlatformSettingsService::class)->adminDefinitions(),
        );
    }

    public function test_live_gateway_is_selected_when_ccapi_driver_configured(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'test-key',
        ]);

        $this->assertTrue(app(PlatformSettingsService::class)->usesLivePaymentGateway());
        $this->assertInstanceOf(
            \App\Services\Payment\CryptoCurrencyApiGateway::class,
            app(\App\Services\Payment\PaymentGatewayInterface::class),
        );
    }
}

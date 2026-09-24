<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Hash;
use App\Services\DepositService;
use App\Services\Payment\MockPaymentGateway;
use App\Services\Payment\PaymentSimulatorService;
use App\Services\WithdrawalService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MockPaymentGatewayFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'mock',
            'coin.payments.mock.auto_complete_payout' => true,
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_deposit_initiate_and_simulated_ipn_credits_wallet(): void
    {
        $user = User::factory()->create();

        $deposit = app(DepositService::class)->initiateWithGateway(
            $user,
            120,
            'USDT',
            app(MockPaymentGateway::class),
        );

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->status);
        $this->assertNotEmpty($deposit->payment_address);
        $this->assertSame(Deposit::gatewayUniqId($deposit->id), $deposit->gateway_uniq_id);

        app(PaymentSimulatorService::class)->simulateDepositIpn($deposit);

        $user->refresh();
        $deposit->refresh();

        $this->assertSame(Deposit::STATUS_CONFIRMED, $deposit->status);
        $this->assertSame('120.00', number_format((float) $user->wallet->available, 2, '.', ''));
    }

    public function test_deposit_below_minimum_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        app(DepositService::class)->createPending($user, 5, 'USDT');
    }

    public function test_btc_deposit_below_usdt_minimum_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        app(DepositService::class)->createPending($user, 10, 'BTC');
    }

    public function test_withdrawal_processing_triggers_mock_gateway_payout_and_ipn(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Ops',
            'email' => 'ops@coin.test',
            'password' => Hash::make('secret1234'),
        ]);
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 500,
            'balance' => 500,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 100);
        $withdrawal = app(WithdrawalService::class)->dispatchViaGateway($withdrawal, $admin);

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->status);
        $this->assertNotEmpty($withdrawal->gateway_request_id);
        $this->assertNotEmpty($withdrawal->txid);
    }

    public function test_user_can_simulate_payout_via_gateway_from_pending_withdrawal(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 300,
            'balance' => 300,
            'pending' => 0,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 80);
        $withdrawal = app(WithdrawalService::class)->simulatePayoutViaGateway($withdrawal);

        $user->refresh();

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->status);
        $this->assertNotEmpty($withdrawal->gateway_request_id);
        $this->assertSame('219.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $user->wallet->pending, 2, '.', ''));
    }
}

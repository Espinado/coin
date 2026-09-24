<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Deposit;
use App\Models\User;
use App\Support\PaymentStatusReason;
use App\Services\PlatformSettingsService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TopUpPaymentPollingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'test-ccapi-key',
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => true]);
    }

    public function test_poll_shows_success_when_live_deposit_is_confirmed(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_CONFIRMED,
            'method' => 'ccapi',
            'payment_address' => 'TAddr123',
            'gateway_uniq_id' => Deposit::gatewayUniqId(99),
            'gateway_network' => 'trx',
            'confirmed_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('paymentModal', 'topup')
            ->set('paymentModalStep', 'payment')
            ->set('pendingDepositId', $deposit->id)
            ->call('pollTopUpPaymentStatus')
            ->assertSet('paymentModalStep', 'success')
            ->assertSet('paymentModalReference', 'TOP-'.$deposit->id)
            ->assertSet('pendingDepositId', null);
    }

    public function test_poll_shows_error_when_live_deposit_is_rejected(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_REJECTED,
            'method' => 'ccapi',
            'payment_address' => 'TAddr123',
            'gateway_uniq_id' => Deposit::gatewayUniqId(100),
            'gateway_network' => 'trx',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('paymentModal', 'topup')
            ->set('paymentModalStep', 'payment')
            ->set('pendingDepositId', $deposit->id)
            ->call('pollTopUpPaymentStatus')
            ->assertSet('paymentModalStep', 'error')
            ->assertSet('paymentModalError', __('coin.payment_reasons.deposit_generic'));
    }

    public function test_poll_shows_amount_mismatch_message_when_received_amount_differs(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'received_amount' => 50,
            'status' => Deposit::STATUS_REJECTED,
            'status_reason' => PaymentStatusReason::DEPOSIT_AMOUNT_MISMATCH,
            'method' => 'ccapi',
            'payment_address' => 'TAddrMismatch',
            'gateway_uniq_id' => Deposit::gatewayUniqId(102),
            'gateway_network' => 'trx',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('paymentModal', 'topup')
            ->set('paymentModalStep', 'payment')
            ->set('pendingDepositId', $deposit->id)
            ->call('pollTopUpPaymentStatus')
            ->assertSet('paymentModalStep', 'error')
            ->assertSet('paymentModalError', __('coin.payment_reasons.deposit_amount_mismatch', [
                'expected' => '100.00',
                'received' => '50.00',
                'currency' => 'USDT',
            ]));
    }

    public function test_poll_is_ignored_in_mock_mode(): void
    {
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => false]);

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_CONFIRMED,
            'method' => 'mock',
            'payment_address' => 'MOCK-ADDR',
            'gateway_uniq_id' => Deposit::gatewayUniqId(101),
            'gateway_network' => 'trx',
            'confirmed_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('paymentModal', 'topup')
            ->set('paymentModalStep', 'payment')
            ->set('pendingDepositId', $deposit->id)
            ->call('pollTopUpPaymentStatus')
            ->assertSet('paymentModalStep', 'payment');
    }
}

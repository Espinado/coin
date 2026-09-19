<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PayoutAddressService;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayoutAddressTest extends TestCase
{
    use RefreshDatabase;

    public const VALID_TRON_ADDRESS = 'TMYBKvQ5qFtp2xqiB8jEGy4vsUPmZ7c9GG';

    public const VALID_BTC_ADDRESS = 'bc1qqhza20mal9tdar863pzrlpjgfx6kdhyfssccpf';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_user_can_save_valid_tron_payout_address(): void
    {
        $user = User::factory()->create();

        app(PayoutAddressService::class)->saveForUser($user, self::VALID_TRON_ADDRESS, 'USDT');

        $user->refresh();

        $this->assertSame(self::VALID_TRON_ADDRESS, $user->wallet->payout_address);
        $this->assertSame('TRC-20', $user->wallet->network_label);
    }

    public function test_user_can_save_valid_bitcoin_payout_address(): void
    {
        $user = User::factory()->create();

        app(PayoutAddressService::class)->saveForUser($user, self::VALID_BTC_ADDRESS, 'BTC');

        $user->refresh();

        $this->assertSame(self::VALID_BTC_ADDRESS, $user->wallet->btc_payout_address);
        $this->assertSame('Bitcoin', $user->wallet->btc_network_label);
    }

    public function test_usdt_withdrawal_rejects_bitcoin_address(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => self::VALID_BTC_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.payout_address_invalid'));

        app(WithdrawalService::class)->createForUser($user, 50, 'USDT');
    }

    public function test_btc_withdrawal_rejects_tron_address(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 200,
            'balance' => 200,
            'btc_payout_address' => self::VALID_TRON_ADDRESS,
            'btc_network_label' => 'Bitcoin',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.btc_payout_address_invalid'));

        app(WithdrawalService::class)->createForUser($user, 0.001, 'BTC');
    }

    public function test_usdt_withdrawal_succeeds_with_valid_tron_address(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => self::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 50, 'USDT');

        $this->assertSame(self::VALID_TRON_ADDRESS, $withdrawal->payout_address);
        $this->assertSame('USDT', $withdrawal->currency);
        $this->assertSame(50.0, (float) $withdrawal->amount);
        $this->assertSame(50.0, $withdrawal->ledgerAmount());
    }

    public function test_btc_withdrawal_succeeds_with_valid_bitcoin_address(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 200,
            'balance' => 200,
            'btc_payout_address' => self::VALID_BTC_ADDRESS,
            'btc_network_label' => 'Bitcoin',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 0.001, 'BTC');

        $this->assertSame(self::VALID_BTC_ADDRESS, $withdrawal->payout_address);
        $this->assertSame('BTC', $withdrawal->currency);
        $this->assertSame(0.001, (float) $withdrawal->amount);
        $this->assertGreaterThan(0, $withdrawal->ledgerAmount());
    }

    public function test_dashboard_wallet_modal_rejects_invalid_usdt_address(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->call('openWalletModal', 'USDT')
            ->set('payoutAddressInput', '0x7c4b912a9f8833e2d1b0c8a4f')
            ->set('payoutAddressConfirm', '0x7c4b912a9f8833e2d1b0c8a4f')
            ->set('payoutAddressPassword', 'password')
            ->call('savePayoutAddress')
            ->assertHasErrors(['payoutAddressInput']);
    }

    public function test_dashboard_wallet_modal_rejects_invalid_btc_address(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->call('openWalletModal', 'BTC')
            ->set('payoutAddressInput', self::VALID_TRON_ADDRESS)
            ->set('payoutAddressConfirm', self::VALID_TRON_ADDRESS)
            ->set('payoutAddressPassword', 'password')
            ->call('savePayoutAddress')
            ->assertHasErrors(['payoutAddressInput']);
    }
}

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_user_can_save_valid_tron_payout_address(): void
    {
        $user = User::factory()->create();

        app(PayoutAddressService::class)->saveForUser($user, self::VALID_TRON_ADDRESS);

        $user->refresh();

        $this->assertSame(self::VALID_TRON_ADDRESS, $user->wallet->payout_address);
        $this->assertSame('TRC-20', $user->wallet->network_label);
    }

    public function test_withdrawal_requires_valid_payout_address(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => '0x7c4b912a9f8833e2d1b0c8a4f',
            'network_label' => 'TRC-20',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.payout_address_invalid'));

        app(WithdrawalService::class)->createForUser($user, 50);
    }

    public function test_withdrawal_succeeds_with_valid_payout_address(): void
    {
        $user = User::factory()->create();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => self::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 50);

        $this->assertSame(self::VALID_TRON_ADDRESS, $withdrawal->payout_address);
    }

    public function test_dashboard_wallet_modal_rejects_invalid_address(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->call('openWalletModal')
            ->set('payoutAddressInput', '0x7c4b912a9f8833e2d1b0c8a4f')
            ->set('payoutAddressConfirm', '0x7c4b912a9f8833e2d1b0c8a4f')
            ->set('payoutAddressPassword', 'password')
            ->call('savePayoutAddress')
            ->assertHasErrors(['payoutAddressInput']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulateCcapiIpnCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.ccapi.api_key' => 'test-ccapi-key',
            'coin.payments.ccapi.min_confirmations' => 1,
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_command_confirms_pending_deposit_via_internal_webhook(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 75,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TSimAddress',
            'gateway_network' => 'trx',
            'gateway_uniq_id' => 'deposit:pending',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);

        $this->artisan('coin:simulate-ccapi-ipn', [
            '--deposit' => $deposit->id,
            '--txid' => 'sim-cli-tx-1',
        ])->assertSuccessful();

        $deposit->refresh();
        $user->refresh();

        $this->assertSame(Deposit::STATUS_CONFIRMED, $deposit->status);
        $this->assertSame('sim-cli-tx-1', $deposit->txid);
        $this->assertSame('75.00', number_format((float) $user->wallet->available, 2, '.', ''));
    }

    public function test_command_is_disabled_in_production_even_with_force(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TSimAddress',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);

        $this->artisan('coin:simulate-ccapi-ipn', [
            '--deposit' => $deposit->id,
            '--force' => true,
        ])->assertFailed();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);
    }

    public function test_command_is_disabled_on_staging_even_with_force(): void
    {
        app()->detectEnvironment(fn () => 'staging');

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TSimAddress',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);

        $this->artisan('coin:simulate-ccapi-ipn', [
            '--deposit' => $deposit->id,
            '--force' => true,
        ])->assertFailed();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);
    }

    public function test_command_dry_run_does_not_confirm_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 10,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TSimAddress',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);

        $this->artisan('coin:simulate-ccapi-ipn', [
            '--deposit' => $deposit->id,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);
        $this->assertSame('0.00', number_format((float) $user->fresh()->wallet->available, 2, '.', ''));
    }
}

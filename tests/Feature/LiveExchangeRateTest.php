<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DepositService;
use App\Services\ExchangeRateService;
use App\Services\PlatformSettingsService;
use App\Services\WithdrawalService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.exchange_rates.coinmarketcap.api_key' => 'test-cmc-key',
            'coin.exchange_rates.coinmarketcap.base_url' => 'https://pro-api.coinmarketcap.com',
            'coin.exchange_rates.coinmarketcap.enabled' => true,
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);

        app(PlatformSettingsService::class)->setMany([
            'usdt_per_btc' => '50000',
            'btc_per_usdt' => '0.00002',
        ]);
    }

    public function test_btc_deposit_confirm_uses_live_rate_at_credit_time(): void
    {
        Http::fake([
            'pro-api.coinmarketcap.com/v3/cryptocurrency/quotes/latest*' => Http::response([
                'data' => [[
                    'id' => 1,
                    'symbol' => 'BTC',
                    'quote' => [[
                        'symbol' => 'USDT',
                        'price' => 80000,
                    ]],
                ]],
            ]),
        ]);

        $user = User::factory()->create();
        $deposit = app(DepositService::class)->createPending($user, 0.0002, 'BTC');

        $this->assertSame('BTC', $deposit->currency);
        $this->assertNull($deposit->exchange_rate);

        app(PlatformSettingsService::class)->setMany([
            'usdt_per_btc' => '100000',
            'btc_per_usdt' => '0.00001',
        ]);

        app(DepositService::class)->confirm($deposit->fresh());

        $user->refresh();
        $deposit->refresh();

        $this->assertSame('16.00', number_format((float) $deposit->credited_amount, 2, '.', ''));
        $this->assertSame('16.00', number_format((float) $user->wallet->available, 2, '.', ''));
    }

    public function test_withdrawal_stores_live_rate_snapshot(): void
    {
        Http::fake([
            'pro-api.coinmarketcap.com/v3/cryptocurrency/quotes/latest*' => Http::response([
                'data' => [[
                    'id' => 1,
                    'symbol' => 'BTC',
                    'quote' => [[
                        'symbol' => 'USDT',
                        'price' => 81292,
                    ]],
                ]],
            ]),
        ]);

        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user, 100);

        $this->assertSame('81292.00000000', number_format((float) $withdrawal->usdt_per_btc, 8, '.', ''));
        $this->assertSame('0.00001230', number_format((float) $withdrawal->exchange_rate, 8, '.', ''));
    }

    public function test_btc_deposit_allows_fractional_amount_when_usdt_equivalent_meets_minimum(): void
    {
        Http::fake([
            'pro-api.coinmarketcap.com/v3/cryptocurrency/quotes/latest*' => Http::response([
                'data' => [[
                    'id' => 1,
                    'symbol' => 'BTC',
                    'quote' => [[
                        'symbol' => 'USDT',
                        'price' => 81264.15,
                    ]],
                ]],
            ]),
        ]);

        $rates = app(ExchangeRateService::class);

        $rates->assertMinDepositAtLiveRate(0.0006, 'BTC');

        $this->expectException(\RuntimeException::class);
        $rates->assertMinDepositAtLiveRate(0.0001, 'BTC');
    }

    public function test_fetch_live_rate_falls_back_to_stored_settings(): void
    {
        config(['coin.exchange_rates.coinmarketcap.enabled' => false]);

        $rates = app(ExchangeRateService::class);

        $this->assertSame(50000.0, $rates->fetchLiveUsdtPerBtc());
    }
}

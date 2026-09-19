<?php

namespace Tests\Feature;

use App\Services\ExchangeRateService;
use App\Services\ExchangeRates\BtcRateSyncService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BtcRateSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.exchange_rates.coinmarketcap.api_key' => 'test-cmc-key',
            'coin.exchange_rates.coinmarketcap.base_url' => 'https://pro-api.coinmarketcap.com',
            'coin.exchange_rates.coinmarketcap.enabled' => true,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_coinmarketcap_sync_stores_btc_usdt_rate(): void
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

        $result = app(BtcRateSyncService::class)->syncFromCoinMarketCap();

        $this->assertSame(80000.0, $result['usdt_per_btc']);
        $this->assertSame(0.0000125, round($result['btc_per_usdt'], 7));

        $rates = app(ExchangeRateService::class);
        $this->assertSame(80000.0, $rates->usdtPerBtc());
        $this->assertSame(0.0000125, round($rates->btcPerUsdt(), 7));

        $conversion = $rates->convertToBase(0.0000125, 'BTC');
        $this->assertSame(1.0, $conversion['amount']);
    }
}

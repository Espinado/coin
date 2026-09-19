<?php

namespace App\Services\ExchangeRates;

use App\Services\PlatformSettingsService;
use RuntimeException;

class BtcRateSyncService
{
    public function __construct(
        private CoinMarketCapClient $coinMarketCap,
        private PlatformSettingsService $settings,
    ) {}

    /**
     * @return array{usdt_per_btc: float, btc_per_usdt: float, updated_at: string}
     */
    public function syncFromCoinMarketCap(): array
    {
        $usdtPerBtc = $this->coinMarketCap->fetchBtcPriceInUsdt();

        if ($usdtPerBtc <= 0) {
            throw new RuntimeException('CoinMarketCap returned an invalid BTC price.');
        }

        $btcPerUsdt = 1 / $usdtPerBtc;
        $updatedAt = now()->toIso8601String();

        $this->settings->setMany([
            'usdt_per_btc' => $this->formatUsdtPerBtc($usdtPerBtc),
            'btc_per_usdt' => $this->formatBtcPerUsdt($btcPerUsdt),
            'btc_rate_updated_at' => $updatedAt,
            'btc_rate_source' => 'coinmarketcap',
        ]);

        return [
            'usdt_per_btc' => $usdtPerBtc,
            'btc_per_usdt' => $btcPerUsdt,
            'updated_at' => $updatedAt,
        ];
    }

    private function formatUsdtPerBtc(float $value): string
    {
        return rtrim(rtrim(number_format($value, 8, '.', ''), '0'), '.');
    }

    private function formatBtcPerUsdt(float $value): string
    {
        return rtrim(rtrim(number_format($value, 16, '.', ''), '0'), '.');
    }
}

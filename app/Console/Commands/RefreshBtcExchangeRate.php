<?php

namespace App\Console\Commands;

use App\Services\ExchangeRates\BtcRateSyncService;
use Illuminate\Console\Command;
use Throwable;

class RefreshBtcExchangeRate extends Command
{
    protected $signature = 'coin:refresh-btc-rate';

    protected $description = 'Fetch BTC/USDT rate from CoinMarketCap and store it in platform settings';

    public function handle(BtcRateSyncService $sync): int
    {
        if (! config('coin.exchange_rates.coinmarketcap.enabled')) {
            $this->warn('CoinMarketCap rate sync is disabled (set CMC_API_KEY to enable).');

            return self::SUCCESS;
        }

        try {
            $result = $sync->syncFromCoinMarketCap();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'BTC rate updated: 1 BTC = %s USDT (stored at %s)',
            number_format($result['usdt_per_btc'], 2, '.', ','),
            $result['updated_at'],
        ));

        return self::SUCCESS;
    }
}

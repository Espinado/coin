<?php

namespace App\Services\ExchangeRates;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CoinMarketCapClient
{
    public function fetchBtcPriceInUsdt(): float
    {
        $apiKey = (string) config('coin.exchange_rates.coinmarketcap.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('CoinMarketCap API key is not configured.');
        }

        $baseUrl = rtrim((string) config('coin.exchange_rates.coinmarketcap.base_url'), '/');

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->withHeaders([
                    'X-CMC_PRO_API_KEY' => $apiKey,
                ])
                ->get($baseUrl.'/v3/cryptocurrency/quotes/latest', [
                    'id' => '1',
                    'convert' => 'USDT',
                ])
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException('CoinMarketCap request failed: '.$exception->getMessage(), previous: $exception);
        }

        $payload = $response->json();
        $assets = $payload['data'] ?? null;

        if (! is_array($assets) || $assets === []) {
            throw new RuntimeException('CoinMarketCap returned an empty BTC quote.');
        }

        $asset = $assets[0] ?? null;
        $quotes = is_array($asset) ? ($asset['quote'] ?? null) : null;

        if (! is_array($quotes)) {
            throw new RuntimeException('CoinMarketCap BTC quote payload is invalid.');
        }

        foreach ($quotes as $quote) {
            if (! is_array($quote)) {
                continue;
            }

            if (strtoupper((string) ($quote['symbol'] ?? '')) !== 'USDT') {
                continue;
            }

            $price = (float) ($quote['price'] ?? 0);

            if ($price > 0) {
                return $price;
            }
        }

        throw new RuntimeException('CoinMarketCap did not return a USDT price for BTC.');
    }
}

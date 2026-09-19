<?php

namespace App\Services;

use RuntimeException;

class ExchangeRateService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function baseCurrency(): string
    {
        return (string) config('coin.wallet.base_currency', 'USDT');
    }

    /** How many BTC equal 1 USDT (demo default: 2 BTC per 1 USDT). */
    public function btcPerUsdt(): float
    {
        $rate = $this->settings->getFloat('btc_per_usdt');

        if ($rate <= 0) {
            $rate = $this->settings->getFloat('btc_per_usd');
        }

        return $rate > 0 ? $rate : 2.0;
    }

    /**
     * @return array{amount: float, rate: ?float, base_currency: string}
     */
    public function convertToBase(float $amount, string $fromCurrency): array
    {
        $from = strtoupper(trim($fromCurrency));
        $base = $this->baseCurrency();

        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        if ($from === $base) {
            return [
                'amount' => round($amount, 2),
                'rate' => null,
                'base_currency' => $base,
            ];
        }

        if ($from === 'BTC') {
            $rate = $this->btcPerUsdt();

            return [
                'amount' => round($amount / $rate, 2),
                'rate' => $rate,
                'base_currency' => $base,
            ];
        }

        throw new RuntimeException("Unsupported deposit currency: {$fromCurrency}");
    }

    public function previewLabel(float $amount, string $fromCurrency): string
    {
        $converted = $this->convertToBase($amount, $fromCurrency);

        return number_format($converted['amount'], 2, '.', ',').' '.$converted['base_currency'];
    }

    public function minDepositUsdt(): float
    {
        return $this->settings->minDeposit();
    }

    public function minDepositAmountIn(string $currency): float
    {
        $minUsdt = $this->minDepositUsdt();
        $from = strtoupper(trim($currency));

        if ($from === $this->baseCurrency()) {
            return $minUsdt;
        }

        if ($from === 'BTC') {
            return round($minUsdt * $this->btcPerUsdt(), 8);
        }

        throw new RuntimeException("Unsupported deposit currency: {$currency}");
    }

    public function formatMinDepositLabel(string $currency): string
    {
        $amount = $this->minDepositAmountIn($currency);
        $symbol = strtoupper(trim($currency));
        $formatted = $symbol === 'BTC'
            ? rtrim(rtrim(number_format($amount, 8, '.', ''), '0'), '.')
            : number_format($amount, 2, '.', '');

        return $formatted.' '.$symbol;
    }

    public function assertMinDeposit(float $amount, string $currency): void
    {
        if ($amount <= 0) {
            throw new RuntimeException(__('coin.wallet.min_deposit_error', [
                'min' => $this->formatMinDepositLabel($currency),
            ]));
        }

        $converted = $this->convertToBase($amount, $currency);

        if ($converted['amount'] + 0.00000001 < $this->minDepositUsdt()) {
            throw new RuntimeException(__('coin.wallet.min_deposit_error', [
                'min' => $this->formatMinDepositLabel($currency),
            ]));
        }
    }
}

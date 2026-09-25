<?php

namespace App\Services;

use App\Support\CryptoAmountFormat;
use App\Services\ExchangeRates\CoinMarketCapClient;
use RuntimeException;
use Throwable;

class ExchangeRateService
{
    public function __construct(
        private PlatformSettingsService $settings,
        private CoinMarketCapClient $coinMarketCap,
    ) {}

    public function baseCurrency(): string
    {
        return (string) config('coin.wallet.base_currency', 'USDT');
    }

    /** USDT price of 1 BTC from platform settings (hourly sync / manual). */
    public function usdtPerBtc(): float
    {
        return $this->storedUsdtPerBtc();
    }

    public function storedUsdtPerBtc(): float
    {
        $rate = $this->settings->getFloat('usdt_per_btc');

        if ($rate > 0) {
            return $rate;
        }

        $legacyBtcPerUsdt = $this->settings->getFloat('btc_per_usdt');

        if ($legacyBtcPerUsdt > 0) {
            return 1 / $legacyBtcPerUsdt;
        }

        $legacyBtcPerUsd = $this->settings->getFloat('btc_per_usd');

        if ($legacyBtcPerUsd > 0) {
            return 1 / $legacyBtcPerUsd;
        }

        return 0;
    }

    /** How many BTC equal 1 USDT from stored settings. */
    public function btcPerUsdt(): float
    {
        return $this->btcPerUsdtFromUsdtRate($this->storedUsdtPerBtc());
    }

    public function btcPerUsdtFromUsdtRate(float $usdtPerBtc): float
    {
        if ($usdtPerBtc > 0) {
            return 1 / $usdtPerBtc;
        }

        return 2.0;
    }

    /** Fetch current BTC/USDT from CoinMarketCap, fallback to stored settings. */
    public function fetchLiveUsdtPerBtc(): float
    {
        if (config('coin.exchange_rates.coinmarketcap.enabled')) {
            try {
                $live = $this->coinMarketCap->fetchBtcPriceInUsdt();

                if ($live > 0) {
                    return $live;
                }
            } catch (Throwable) {
                // Fall back to the last stored hourly rate.
            }
        }

        $stored = $this->storedUsdtPerBtc();

        if ($stored > 0) {
            return $stored;
        }

        throw new RuntimeException('BTC exchange rate is unavailable.');
    }

    public function fetchLiveBtcPerUsdt(): float
    {
        return $this->btcPerUsdtFromUsdtRate($this->fetchLiveUsdtPerBtc());
    }

    public function btcRateUpdatedAt(): ?string
    {
        $value = trim($this->settings->get('btc_rate_updated_at'));

        return $value !== '' ? $value : null;
    }

    public function formatBtcMarketRateLabel(): ?string
    {
        $usdtPerBtc = $this->usdtPerBtc();

        if ($usdtPerBtc <= 0) {
            return null;
        }

        return number_format($usdtPerBtc, 2, '.', ',').' USDT';
    }

    /**
     * @return array{amount: float, rate: ?float, usdt_per_btc: ?float, base_currency: string}
     */
    public function convertToBase(float $amount, string $fromCurrency, ?float $btcPerUsdt = null, ?float $usdtPerBtc = null): array
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
                'usdt_per_btc' => null,
                'base_currency' => $base,
            ];
        }

        if ($from === 'BTC') {
            $resolvedUsdtPerBtc = $usdtPerBtc ?? ($btcPerUsdt !== null && $btcPerUsdt > 0 ? 1 / $btcPerUsdt : 0);
            $rate = $btcPerUsdt ?? ($resolvedUsdtPerBtc > 0 ? 1 / $resolvedUsdtPerBtc : $this->btcPerUsdt());

            return [
                'amount' => round($amount / $rate, 2),
                'rate' => $rate,
                'usdt_per_btc' => $resolvedUsdtPerBtc > 0 ? $resolvedUsdtPerBtc : ($rate > 0 ? 1 / $rate : null),
                'base_currency' => $base,
            ];
        }

        throw new RuntimeException("Unsupported deposit currency: {$fromCurrency}");
    }

    public function convertToBaseAtLiveRate(float $amount, string $fromCurrency): array
    {
        if (strtoupper(trim($fromCurrency)) === 'BTC') {
            $usdtPerBtc = $this->fetchLiveUsdtPerBtc();

            return $this->convertToBase($amount, $fromCurrency, null, $usdtPerBtc);
        }

        return $this->convertToBase($amount, $fromCurrency);
    }

    public function previewLabel(float $amount, string $fromCurrency): string
    {
        $converted = $this->convertToBase($amount, $fromCurrency);

        return number_format($converted['amount'], 2, '.', ',').' '.$converted['base_currency'];
    }

    /**
     * Normalize a user-entered top-up into the USDT amount sent to the payment gateway.
     *
     * @return array{
     *     pay_amount: float,
     *     pay_currency: string,
     *     input_amount: ?float,
     *     input_currency: ?string,
     *     exchange_rate: ?float,
     *     usdt_per_btc: ?float
     * }
     */
    public function prepareGatewayDeposit(float $amount, string $inputCurrency, bool $assertMinimum = true): array
    {
        $input = strtoupper(trim($inputCurrency));
        $base = $this->baseCurrency();
        $allowed = config('coin.deposits.currencies', ['USDT', 'BTC']);

        if (! in_array($input, $allowed, true)) {
            throw new RuntimeException('Unsupported top-up currency.');
        }

        if ($input === $base) {
            if ($assertMinimum) {
                $this->assertMinDeposit($amount, $input);
            }

            return [
                'pay_amount' => round($amount, 2),
                'pay_currency' => $base,
                'input_amount' => null,
                'input_currency' => null,
                'exchange_rate' => null,
                'usdt_per_btc' => null,
            ];
        }

        $usdtPerBtc = $this->fetchLiveUsdtPerBtc();
        $btcPerUsdt = $this->btcPerUsdtFromUsdtRate($usdtPerBtc);

        if ($assertMinimum) {
            $this->assertMinDeposit($amount, $input, $btcPerUsdt);
        }

        $conversion = $this->convertToBase($amount, $input, $btcPerUsdt, $usdtPerBtc);

        return [
            'pay_amount' => $conversion['amount'],
            'pay_currency' => $base,
            'input_amount' => $amount,
            'input_currency' => $input,
            'exchange_rate' => $btcPerUsdt,
            'usdt_per_btc' => $usdtPerBtc,
        ];
    }

    public function prepareGatewayDepositAtLiveRate(float $amount, string $inputCurrency, bool $assertMinimum = true): array
    {
        return $this->prepareGatewayDeposit($amount, $inputCurrency, $assertMinimum);
    }

    public function minDepositUsdt(): float
    {
        return $this->settings->minDeposit();
    }

    public function minDepositAmountIn(string $currency, ?float $btcPerUsdt = null): float
    {
        $minUsdt = $this->minDepositUsdt();
        $from = strtoupper(trim($currency));

        if ($from === $this->baseCurrency()) {
            return $minUsdt;
        }

        if ($from === 'BTC') {
            $rate = $btcPerUsdt ?? $this->btcPerUsdt();

            return round($minUsdt * $rate, 8);
        }

        throw new RuntimeException("Unsupported deposit currency: {$currency}");
    }

    public function formatMinDepositLabel(string $currency, ?float $btcPerUsdt = null): string
    {
        $amount = $this->minDepositAmountIn($currency, $btcPerUsdt);
        $symbol = strtoupper(trim($currency));

        return CryptoAmountFormat::amountWithSymbol($amount, $symbol);
    }

    public function assertMinDeposit(float $amount, string $currency, ?float $btcPerUsdt = null): void
    {
        if ($amount <= 0) {
            throw new RuntimeException(__('coin.wallet.min_deposit_error', [
                'min' => $this->formatMinDepositLabel($currency, $btcPerUsdt),
            ]));
        }

        $converted = $btcPerUsdt !== null
            ? $this->convertToBase($amount, $currency, $btcPerUsdt)
            : $this->convertToBase($amount, $currency);

        if ($converted['amount'] + 0.00000001 < $this->minDepositUsdt()) {
            throw new RuntimeException(__('coin.wallet.min_deposit_error', [
                'min' => $this->formatMinDepositLabel($currency, $btcPerUsdt),
            ]));
        }
    }

    public function assertMinDepositAtLiveRate(float $amount, string $currency): void
    {
        if (strtoupper(trim($currency)) === 'BTC') {
            $this->assertMinDeposit($amount, $currency, $this->fetchLiveBtcPerUsdt());

            return;
        }

        $this->assertMinDeposit($amount, $currency);
    }

    public function minWithdrawalUsdt(): float
    {
        return $this->settings->minWithdrawal();
    }

    public function minWithdrawalAmountIn(string $currency, ?float $btcPerUsdt = null): float
    {
        return $this->minDepositAmountIn($currency, $btcPerUsdt);
    }

    public function formatMinWithdrawalLabel(string $currency, ?float $btcPerUsdt = null): string
    {
        $minUsdt = $this->minWithdrawalUsdt();
        $from = strtoupper(trim($currency));

        if ($from === $this->baseCurrency()) {
            return number_format($minUsdt, 2, '.', '').' '.$from;
        }

        if ($from === 'BTC') {
            $rate = $btcPerUsdt ?? $this->btcPerUsdt();
            $amount = round($minUsdt * $rate, 8);

            return rtrim(rtrim(number_format($amount, 8, '.', ''), '0'), '.').' BTC';
        }

        throw new RuntimeException("Unsupported withdrawal currency: {$currency}");
    }

    public function assertMinWithdrawal(float $amount, string $currency, ?float $btcPerUsdt = null): void
    {
        if ($amount <= 0) {
            throw new RuntimeException(__('coin.wallet.min_withdrawal_error', [
                'min' => $this->formatMinWithdrawalLabel($currency, $btcPerUsdt),
            ]));
        }

        $converted = $btcPerUsdt !== null
            ? $this->convertToBase($amount, $currency, $btcPerUsdt)
            : $this->convertToBase($amount, $currency);

        if ($converted['amount'] + 0.00000001 < $this->minWithdrawalUsdt()) {
            throw new RuntimeException(__('coin.wallet.min_withdrawal_error', [
                'min' => $this->formatMinWithdrawalLabel($currency, $btcPerUsdt),
            ]));
        }
    }

    public function assertMinWithdrawalAtLiveRate(float $amount, string $currency): void
    {
        if (strtoupper(trim($currency)) === 'BTC') {
            $this->assertMinWithdrawal($amount, $currency, $this->fetchLiveBtcPerUsdt());

            return;
        }

        $this->assertMinWithdrawal($amount, $currency);
    }

    public function withdrawDebitPreviewLabel(float $amount, string $fromCurrency): string
    {
        $converted = $this->convertToBase($amount, $fromCurrency);

        return number_format($converted['amount'], 2, '.', ',').' '.$converted['base_currency'];
    }

    public function convertFromBase(float $amountUsdt, string $toCurrency, ?float $btcPerUsdt = null): float
    {
        $to = strtoupper(trim($toCurrency));

        if ($amountUsdt <= 0) {
            return 0;
        }

        if ($to === $this->baseCurrency()) {
            return round($amountUsdt, 2);
        }

        if ($to === 'BTC') {
            $rate = $btcPerUsdt ?? $this->btcPerUsdt();

            return round($amountUsdt * $rate, 8);
        }

        throw new RuntimeException("Unsupported withdrawal currency: {$toCurrency}");
    }
}

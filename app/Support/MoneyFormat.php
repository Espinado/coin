<?php

namespace App\Support;

final class MoneyFormat
{
    public static function currency(?string $currency = null): string
    {
        return $currency ?: (string) config('coin.wallet.base_currency', 'USDT');
    }

    public static function amount(float|int|string|null $value, ?string $currency = null, int $decimals = 2): string
    {
        return number_format((float) ($value ?? 0), $decimals, '.', ',').' '.self::currency($currency);
    }

    public static function signedAmount(float|int|string|null $value, ?string $currency = null, int $decimals = 2): string
    {
        $float = (float) ($value ?? 0);
        $prefix = $float >= 0 ? '+' : '-';

        return $prefix.number_format(abs($float), $decimals, '.', ',').' '.self::currency($currency);
    }

    public static function zero(?string $currency = null): string
    {
        return self::amount(0, $currency);
    }
}

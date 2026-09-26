<?php

namespace App\Support;

final class CryptoAmountFormat
{
    public static function decimals(string $currency): int
    {
        return strtoupper(trim($currency)) === 'BTC' ? 8 : 2;
    }

    public static function normalize(float|string|null $amount, string $currency): float
    {
        if ($amount === null || $amount === '') {
            return 0.0;
        }

        return (float) number_format((float) $amount, self::decimals($currency), '.', '');
    }

    public static function format(float|string|null $amount, string $currency, bool $trimTrailingZeros = true): string
    {
        if ($amount === null || $amount === '') {
            return self::placeholder($currency);
        }

        $decimals = self::decimals($currency);
        $formatted = number_format((float) $amount, $decimals, '.', ',');

        if ($trimTrailingZeros && $decimals === 8) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted;
    }

    public static function formatPlain(float|string|null $amount, string $currency, bool $trimTrailingZeros = true): string
    {
        if ($amount === null || $amount === '') {
            return self::placeholder($currency);
        }

        $decimals = self::decimals($currency);
        $formatted = number_format((float) $amount, $decimals, '.', '');

        if ($trimTrailingZeros && $decimals === 8) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted;
    }

    public static function amountWithSymbol(float|string|null $amount, string $currency): string
    {
        return self::format($amount, $currency).' '.strtoupper(trim($currency));
    }

    public static function placeholder(string $currency): string
    {
        return str_repeat('0', 1).'.'.str_repeat('0', self::decimals($currency));
    }
}

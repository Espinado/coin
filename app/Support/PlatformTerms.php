<?php

namespace App\Support;

final class PlatformTerms
{
    /** Wallet funding — USDT credited to available balance. */
    public const TX_TOP_UP = 'Top-up';

    /** Plan purchase — principal locked in an active contract. */
    public const TX_INVESTMENT = 'Investment';

    /** Outbound transfer to user's external wallet. */
    public const TX_PAYOUT = 'Payout';

    public static function displayTransactionType(string $type): string
    {
        $normalized = match ($type) {
            'Deposit', self::TX_TOP_UP => self::TX_TOP_UP,
            'Withdrawal', self::TX_PAYOUT => self::TX_PAYOUT,
            'Plan purchase', self::TX_INVESTMENT => self::TX_INVESTMENT,
            default => $type,
        };

        $key = 'coin.tx.'.$normalized;

        return __($key) !== $key ? __($key) : $normalized;
    }
}

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
        return match ($type) {
            'Deposit', self::TX_TOP_UP => self::TX_TOP_UP,
            'Withdrawal', self::TX_PAYOUT => self::TX_PAYOUT,
            'Plan purchase', self::TX_INVESTMENT => self::TX_INVESTMENT,
            default => $type,
        };
    }
}

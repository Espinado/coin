<?php

namespace App\Support;

final class PlatformTerms
{
    /** Wallet funding — USDT credited to available balance. */
    public const TX_TOP_UP = 'Top-up';

    /** Plan purchase — principal locked in an active contract. */
    public const TX_INVESTMENT = 'Investment';

    /** Top-up when upgrading an active contract to a higher plan. */
    public const TX_PLAN_UPGRADE = 'Plan upgrade';

    /** Outbound transfer to user's external wallet. */
    public const TX_PAYOUT = 'Payout';

    public static function displayTransactionType(string $type): string
    {
        $normalized = match ($type) {
            'Deposit', self::TX_TOP_UP => self::TX_TOP_UP,
            'Withdrawal', self::TX_PAYOUT => self::TX_PAYOUT,
            'Plan purchase', self::TX_INVESTMENT => self::TX_INVESTMENT,
            'Plan upgrade', self::TX_PLAN_UPGRADE => self::TX_PLAN_UPGRADE,
            default => $type,
        };

        $key = 'coin.tx.'.$normalized;

        return __($key) !== $key ? __($key) : $normalized;
    }

    public static function displayTransactionStatus(string $status): string
    {
        $key = 'coin.tx_status.'.$status;

        return __($key) !== $key ? __($key) : $status;
    }

    public static function displayTransactionSource(string $source): string
    {
        $key = match ($source) {
            'Mock top-up' => 'coin.tx_sources.mock_top_up',
            'Node' => 'coin.plan_names.node',
            'Core' => 'coin.plan_names.core',
            'Cluster' => 'coin.plan_names.cluster',
            'Enterprise' => 'coin.plan_names.enterprise',
            default => null,
        };

        if ($key !== null) {
            return __($key) !== $key ? __($key) : $source;
        }

        $infra = PlanLabels::infra($source);

        return $infra !== $source ? $infra : $source;
    }
}

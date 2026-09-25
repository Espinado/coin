<?php

namespace App\Support;

use App\Models\Deposit;
use App\Models\Withdrawal;

final class PaymentStatusReason
{
    public const DEPOSIT_EXPIRED = 'deposit_expired';

    public const DEPOSIT_AMOUNT_MISMATCH = 'deposit_amount_mismatch';

    public const DEPOSIT_ADDRESS_MISMATCH = 'deposit_address_mismatch';

    public const DEPOSIT_NETWORK_MISMATCH = 'deposit_network_mismatch';

    public const DEPOSIT_CURRENCY_MISMATCH = 'deposit_currency_mismatch';

    public const DEPOSIT_DUPLICATE_TXID = 'deposit_duplicate_txid';

    public const DEPOSIT_GENERIC = 'deposit_generic';

    public const WITHDRAWAL_ADMIN_REJECTED = 'withdrawal_admin_rejected';

    public const WITHDRAWAL_GATEWAY_FAILED = 'withdrawal_gateway_failed';

    public const WITHDRAWAL_MOCK_GATEWAY = 'withdrawal_mock_gateway';

    public const WITHDRAWAL_POLL_STUCK = 'withdrawal_poll_stuck';

    public const WITHDRAWAL_IPN_MISMATCH = 'withdrawal_ipn_mismatch';

    public const WITHDRAWAL_GENERIC = 'withdrawal_generic';

    /** @var array<string, string> */
    private const DEPOSIT_VALIDATION_MAP = [
        'Deposit payment window expired.' => self::DEPOSIT_EXPIRED,
        'Payment address mismatch.' => self::DEPOSIT_ADDRESS_MISMATCH,
        'Blockchain network mismatch.' => self::DEPOSIT_NETWORK_MISMATCH,
        'Currency/token mismatch.' => self::DEPOSIT_CURRENCY_MISMATCH,
        'Currency mismatch.' => self::DEPOSIT_CURRENCY_MISMATCH,
    ];

    public static function depositReasonFromValidation(?string $validationError): ?string
    {
        if ($validationError === null || $validationError === '') {
            return null;
        }

        return self::DEPOSIT_VALIDATION_MAP[$validationError] ?? null;
    }

    public static function depositMessage(Deposit $deposit): string
    {
        $reason = $deposit->status_reason ?: self::DEPOSIT_GENERIC;

        return match ($reason) {
            self::DEPOSIT_EXPIRED => __('coin.payment_reasons.deposit_expired'),
            self::DEPOSIT_AMOUNT_MISMATCH => __('coin.payment_reasons.deposit_amount_mismatch', [
                'expected' => number_format((float) $deposit->amount, self::depositAmountDecimals($deposit), '.', ''),
                'received' => number_format((float) ($deposit->received_amount ?? 0), self::depositAmountDecimals($deposit), '.', ''),
                'currency' => strtoupper((string) $deposit->currency),
            ]),
            self::DEPOSIT_ADDRESS_MISMATCH => __('coin.payment_reasons.deposit_address_mismatch'),
            self::DEPOSIT_NETWORK_MISMATCH => __('coin.payment_reasons.deposit_network_mismatch'),
            self::DEPOSIT_CURRENCY_MISMATCH => __('coin.payment_reasons.deposit_currency_mismatch'),
            self::DEPOSIT_DUPLICATE_TXID => __('coin.payment_reasons.deposit_duplicate_txid'),
            default => __('coin.payment_reasons.deposit_generic'),
        };
    }

    public static function depositShortMessage(Deposit $deposit): string
    {
        $reason = $deposit->status_reason ?: self::DEPOSIT_GENERIC;

        return match ($reason) {
            self::DEPOSIT_EXPIRED => __('coin.payment_reasons_short.deposit_expired'),
            self::DEPOSIT_AMOUNT_MISMATCH => __('coin.payment_reasons_short.deposit_amount_mismatch'),
            self::DEPOSIT_ADDRESS_MISMATCH => __('coin.payment_reasons_short.deposit_address_mismatch'),
            self::DEPOSIT_NETWORK_MISMATCH => __('coin.payment_reasons_short.deposit_network_mismatch'),
            self::DEPOSIT_CURRENCY_MISMATCH => __('coin.payment_reasons_short.deposit_currency_mismatch'),
            self::DEPOSIT_DUPLICATE_TXID => __('coin.payment_reasons_short.deposit_duplicate_txid'),
            default => __('coin.payment_reasons_short.deposit_generic'),
        };
    }

    public static function withdrawalMessage(Withdrawal $withdrawal): string
    {
        $reason = $withdrawal->status_reason ?: self::WITHDRAWAL_GENERIC;

        return match ($reason) {
            self::WITHDRAWAL_ADMIN_REJECTED => __('coin.payment_reasons.withdrawal_admin_rejected'),
            self::WITHDRAWAL_MOCK_GATEWAY => __('coin.payment_reasons.withdrawal_mock_gateway'),
            self::WITHDRAWAL_POLL_STUCK => __('coin.payment_reasons.withdrawal_poll_stuck'),
            self::WITHDRAWAL_IPN_MISMATCH => __('coin.payment_reasons.withdrawal_ipn_mismatch'),
            self::WITHDRAWAL_GATEWAY_FAILED => trim(__('coin.payment_reasons.withdrawal_gateway_failed', [
                'state' => $withdrawal->gateway_state ?? '?',
            ]).' '.self::withdrawalGatewayDetail($withdrawal)),
            default => __('coin.payment_reasons.withdrawal_generic'),
        };
    }

    public static function withdrawalShortMessage(Withdrawal $withdrawal): string
    {
        $reason = $withdrawal->status_reason ?: self::WITHDRAWAL_GENERIC;

        return match ($reason) {
            self::WITHDRAWAL_ADMIN_REJECTED => __('coin.payment_reasons_short.withdrawal_admin_rejected'),
            self::WITHDRAWAL_MOCK_GATEWAY => __('coin.payment_reasons_short.withdrawal_mock_gateway'),
            self::WITHDRAWAL_POLL_STUCK => __('coin.payment_reasons_short.withdrawal_poll_stuck'),
            self::WITHDRAWAL_IPN_MISMATCH => __('coin.payment_reasons_short.withdrawal_ipn_mismatch'),
            self::WITHDRAWAL_GATEWAY_FAILED => __('coin.payment_reasons_short.withdrawal_gateway_failed'),
            default => __('coin.payment_reasons_short.withdrawal_generic'),
        };
    }

    private static function depositAmountDecimals(Deposit $deposit): int
    {
        return strtoupper((string) $deposit->currency) === 'BTC' ? 8 : 2;
    }

    private static function withdrawalGatewayDetail(Withdrawal $withdrawal): string
    {
        $summary = (string) ($withdrawal->gateway_poll_summary ?? '');

        if (str_contains($summary, 'result=OUT_OF_ENERGY')) {
            return __('coin.payment_reasons.withdrawal_gateway_detail_out_of_energy');
        }

        return '';
    }
}

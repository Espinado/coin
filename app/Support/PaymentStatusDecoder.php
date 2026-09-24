<?php

namespace App\Support;

use App\Models\Deposit;
use App\Models\Withdrawal;

final class PaymentStatusDecoder
{
    public static function depositStatus(?string $status): string
    {
        return match ($status) {
            Deposit::STATUS_PENDING => __('coin.admin.deposit_status_pending'),
            Deposit::STATUS_CONFIRMED => __('coin.admin.deposit_status_confirmed'),
            Deposit::STATUS_REJECTED => __('coin.admin.deposit_status_rejected'),
            default => $status !== null && $status !== '' ? ucfirst($status) : '—',
        };
    }

    public static function withdrawalStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        return Withdrawal::adminStatuses()[$status] ?? ucfirst($status);
    }

    public static function transitionLabel(?string $previous, ?string $next, string $entityType): string
    {
        if ($previous === null && $next === null) {
            return '—';
        }

        $decode = $entityType === 'deposit'
            ? self::depositStatus(...)
            : self::withdrawalStatus(...);

        if ($previous === null) {
            return $decode($next);
        }

        if ($next === null || $previous === $next) {
            return $decode($previous);
        }

        return $decode($previous).' → '.$decode($next);
    }

    public static function ccapiPayoutState(?string $state, ?string $result = null): string
    {
        if ($state === null || $state === '') {
            return $result ? self::ccapiPayoutResult($result) : '—';
        }

        $label = match ($state) {
            '4' => __('coin.payment_log.ccapi_state_4'),
            '7' => __('coin.payment_log.ccapi_state_7'),
            '8' => __('coin.payment_log.ccapi_state_8'),
            '9' => __('coin.payment_log.ccapi_state_9'),
            'poll_stuck' => __('coin.payment_log.ccapi_state_poll_stuck'),
            'mock_gateway_reference' => __('coin.payment_log.ccapi_state_mock_gateway'),
            default => __('coin.payment_log.ccapi_state_unknown', ['state' => $state]),
        };

        if ($result !== null && $result !== '') {
            return $label.' · '.self::ccapiPayoutResult($result);
        }

        return $label;
    }

    public static function ccapiPayoutResult(?string $result): string
    {
        if ($result === null || $result === '') {
            return '—';
        }

        return match (strtoupper($result)) {
            'OUT_OF_ENERGY' => __('coin.payment_log.ccapi_result_out_of_energy'),
            default => __('coin.payment_log.ccapi_result_generic', ['result' => $result]),
        };
    }

    public static function pollError(?string $error): string
    {
        if ($error === null || $error === '') {
            return '—';
        }

        return match ($error) {
            'id_or_label_required' => __('coin.payment_log.poll_error_id_or_label_required'),
            default => __('coin.payment_log.poll_error_generic', ['error' => $error]),
        };
    }

    public static function webhookResult(?string $result): string
    {
        return match ($result) {
            'processed' => __('coin.payment_log.result_processed'),
            'ignored' => __('coin.payment_log.result_ignored'),
            'failed' => __('coin.payment_log.result_failed'),
            'duplicate' => __('coin.payment_log.result_duplicate'),
            'info' => __('coin.payment_log.result_info'),
            default => $result ?? '—',
        };
    }

    public static function sourceLabel(?string $source): string
    {
        return match ($source) {
            'ipn' => __('coin.payment_log.source_ipn'),
            'poll' => __('coin.payment_log.source_poll'),
            'app' => __('coin.payment_log.source_app'),
            'admin' => __('coin.payment_log.source_admin'),
            'simulator' => __('coin.payment_log.source_simulator'),
            default => $source ?? '—',
        };
    }

    public static function entityTypeLabel(?string $entityType): string
    {
        return match ($entityType) {
            'deposit' => __('coin.payment_log.entity_deposit'),
            'withdrawal' => __('coin.payment_log.entity_withdrawal'),
            default => $entityType ?? '—',
        };
    }

    public static function decodeWebhookMessage(string $processingResult, ?array $payload = null): array
    {
        [$result, $message] = self::splitProcessingResult($processingResult);

        $gatewayState = is_array($payload) ? (string) ($payload['state'] ?? '') : '';
        $gatewayResult = is_array($payload) ? (string) ($payload['result'] ?? ($payload['error'] ?? '')) : '';

        if (str_starts_with($message, 'Poll error: ')) {
            $error = trim(substr($message, strlen('Poll error: ')));

            return [
                'title' => __('coin.payment_log.title_poll_error'),
                'message' => self::pollError($error),
                'gateway_state' => $gatewayState !== '' ? $gatewayState : null,
                'gateway_result' => $gatewayResult !== '' ? $gatewayResult : null,
            ];
        }

        if (str_contains($message, 'Awaiting payout confirmation')) {
            return [
                'title' => __('coin.payment_log.title_poll_pending'),
                'message' => self::ccapiPayoutState($gatewayState !== '' ? $gatewayState : self::extractStateFromMessage($message)),
                'gateway_state' => $gatewayState !== '' ? $gatewayState : null,
                'gateway_result' => $gatewayResult !== '' ? $gatewayResult : null,
            ];
        }

        if (str_contains($message, 'Gateway reported failed payout')) {
            return [
                'title' => __('coin.payment_log.title_gateway_failed'),
                'message' => self::ccapiPayoutState($gatewayState !== '' ? $gatewayState : self::extractStateFromMessage($message), $gatewayResult),
                'gateway_state' => $gatewayState !== '' ? $gatewayState : null,
                'gateway_result' => $gatewayResult !== '' ? $gatewayResult : null,
            ];
        }

        if (str_contains($message, 'Marked paid')) {
            return [
                'title' => __('coin.payment_log.title_payout_paid'),
                'message' => self::ccapiPayoutState($gatewayState !== '' ? $gatewayState : '7', $gatewayResult),
                'gateway_state' => $gatewayState !== '' ? $gatewayState : '7',
                'gateway_result' => $gatewayResult !== '' ? $gatewayResult : null,
            ];
        }

        if (str_contains($message, 'Deposit confirmed')) {
            return [
                'title' => __('coin.payment_log.title_deposit_confirmed'),
                'message' => $message,
                'gateway_state' => null,
                'gateway_result' => null,
            ];
        }

        if (str_contains($message, 'Deposit rejected')) {
            return [
                'title' => __('coin.payment_log.title_deposit_rejected'),
                'message' => $message,
                'gateway_state' => null,
                'gateway_result' => null,
            ];
        }

        if (str_contains($message, 'Rejected stale MOCK')) {
            return [
                'title' => __('coin.payment_log.title_mock_abandoned'),
                'message' => __('coin.payment_log.message_mock_abandoned'),
                'gateway_state' => 'mock_gateway_reference',
                'gateway_result' => null,
            ];
        }

        if (str_contains($message, 'Rejected after repeated CCAPI poll errors')) {
            return [
                'title' => __('coin.payment_log.title_poll_abandoned'),
                'message' => __('coin.payment_log.message_poll_abandoned'),
                'gateway_state' => 'poll_stuck',
                'gateway_result' => null,
            ];
        }

        return [
            'title' => self::webhookResult($result),
            'message' => $message,
            'gateway_state' => $gatewayState !== '' ? $gatewayState : null,
            'gateway_result' => $gatewayResult !== '' ? $gatewayResult : null,
        ];
    }

    /** @return array{0: ?string, 1: string} */
    public static function splitProcessingResult(?string $processingResult): array
    {
        $processingResult = trim((string) $processingResult);

        if ($processingResult === '') {
            return [null, ''];
        }

        $colon = strpos($processingResult, ':');

        if ($colon === false) {
            return [null, $processingResult];
        }

        return [
            trim(substr($processingResult, 0, $colon)),
            trim(substr($processingResult, $colon + 1)),
        ];
    }

    private static function extractStateFromMessage(string $message): ?string
    {
        if (preg_match('/state\s+(\d+)/i', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

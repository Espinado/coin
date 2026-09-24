<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\PaymentWebhookLog;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\PaymentStatusDecoder;
use App\Support\PaymentStatusReason;

class PaymentStatusLogService
{
    public function logDepositStatusChange(
        Deposit $deposit,
        string $source,
        string $eventType,
        ?string $previousStatus,
        string $newStatus,
        ?string $result = 'info',
        ?string $title = null,
        ?string $message = null,
        ?array $payload = null,
        ?string $gatewayState = null,
        ?string $gatewayResult = null,
    ): PaymentStatusLog {
        $deposit->loadMissing('user');

        return PaymentStatusLog::query()->create([
            'entity_type' => 'deposit',
            'deposit_id' => $deposit->id,
            'user_id' => $deposit->user_id,
            'reference' => 'TOP-'.$deposit->id,
            'source' => $source,
            'event_type' => $eventType,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'gateway' => $deposit->method,
            'gateway_state' => $gatewayState,
            'gateway_result' => $gatewayResult,
            'status_reason' => $deposit->status_reason,
            'result' => $result,
            'title' => $title ?? __('coin.payment_log.title_status_change'),
            'message' => $message ?? PaymentStatusDecoder::transitionLabel($previousStatus, $newStatus, 'deposit'),
            'payload' => $payload,
        ]);
    }

    public function logWithdrawalStatusChange(
        Withdrawal $withdrawal,
        string $source,
        string $eventType,
        ?string $previousStatus,
        string $newStatus,
        ?string $result = 'info',
        ?string $title = null,
        ?string $message = null,
        ?array $payload = null,
        ?string $gatewayState = null,
        ?string $gatewayResult = null,
        ?string $statusReason = null,
    ): PaymentStatusLog {
        $withdrawal->loadMissing('user');

        return PaymentStatusLog::query()->create([
            'entity_type' => 'withdrawal',
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $withdrawal->user_id,
            'reference' => $withdrawal->reference,
            'source' => $source,
            'event_type' => $eventType,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'gateway' => (string) config('coin.payments.driver', 'mock'),
            'gateway_state' => $gatewayState ?? $withdrawal->gateway_state,
            'gateway_result' => $gatewayResult,
            'status_reason' => $statusReason ?? $withdrawal->status_reason,
            'result' => $result,
            'title' => $title ?? __('coin.payment_log.title_status_change'),
            'message' => $message ?? PaymentStatusDecoder::transitionLabel($previousStatus, $newStatus, 'withdrawal'),
            'payload' => $payload,
        ]);
    }

    public function recordFromWebhookLog(PaymentWebhookLog $log, bool $force = false): ?PaymentStatusLog
    {
        if (! filled($log->processing_result)) {
            return null;
        }

        $payload = is_array($log->payload) ? $log->payload : [];
        $payload['webhook_log_id'] = $log->id;

        if (PaymentStatusLog::query()->where('payload->webhook_log_id', $log->id)->exists()) {
            return null;
        }

        if (! $force) {
            [$result] = PaymentStatusDecoder::splitProcessingResult($log->processing_result);

            if ($result === PaymentWebhookLog::RESULT_PROCESSED && ($log->deposit_id || $log->withdrawal_id)) {
                return null;
            }
        }

        $decoded = PaymentStatusDecoder::decodeWebhookMessage(
            (string) $log->processing_result,
            is_array($log->payload) ? $log->payload : null,
        );

        [$result] = PaymentStatusDecoder::splitProcessingResult($log->processing_result);

        $entityType = $log->deposit_id !== null ? 'deposit' : ($log->withdrawal_id !== null ? 'withdrawal' : null);

        if ($entityType === null) {
            return PaymentStatusLog::query()->create([
                'entity_type' => 'deposit',
                'source' => $log->event_type === 'payout_poll' ? PaymentStatusLog::SOURCE_POLL : PaymentStatusLog::SOURCE_IPN,
                'event_type' => $log->event_type,
                'gateway' => $log->gateway,
                'gateway_state' => $decoded['gateway_state'],
                'gateway_result' => $decoded['gateway_result'],
                'result' => $result,
                'title' => $decoded['title'],
                'message' => $decoded['message'],
                'payload' => $payload,
                'created_at' => $log->processed_at ?? $log->created_at,
            ]);
        }

        $deposit = $log->deposit_id ? Deposit::query()->find($log->deposit_id) : null;
        $withdrawal = $log->withdrawal_id ? Withdrawal::query()->find($log->withdrawal_id) : null;

        return PaymentStatusLog::query()->create([
            'entity_type' => $entityType,
            'deposit_id' => $log->deposit_id,
            'withdrawal_id' => $log->withdrawal_id,
            'user_id' => $deposit?->user_id ?? $withdrawal?->user_id,
            'reference' => $deposit ? 'TOP-'.$deposit->id : $withdrawal?->reference,
            'source' => $log->event_type === 'payout_poll' || str_contains((string) $log->event_type, 'poll')
                ? PaymentStatusLog::SOURCE_POLL
                : PaymentStatusLog::SOURCE_IPN,
            'event_type' => $log->event_type,
            'new_status' => $deposit?->status ?? $withdrawal?->status,
            'gateway' => $log->gateway,
            'gateway_state' => $decoded['gateway_state'],
            'gateway_result' => $decoded['gateway_result'],
            'status_reason' => $deposit?->status_reason ?? $withdrawal?->status_reason,
            'result' => $result,
            'title' => $decoded['title'],
            'message' => $decoded['message'],
            'payload' => $payload,
            'created_at' => $log->processed_at ?? $log->created_at,
        ]);
    }

    public function depositCreated(Deposit $deposit): PaymentStatusLog
    {
        return $this->logDepositStatusChange(
            $deposit,
            PaymentStatusLog::SOURCE_APP,
            'created',
            null,
            Deposit::STATUS_PENDING,
            'info',
            __('coin.payment_log.title_deposit_created'),
            __('coin.payment_log.message_deposit_created', [
                'amount' => $deposit->formattedAmount(),
            ]),
        );
    }

    public function depositConfirmed(Deposit $deposit, string $source = PaymentStatusLog::SOURCE_APP): PaymentStatusLog
    {
        return $this->logDepositStatusChange(
            $deposit,
            $source,
            'status_change',
            Deposit::STATUS_PENDING,
            Deposit::STATUS_CONFIRMED,
            'processed',
            __('coin.payment_log.title_deposit_confirmed'),
            __('coin.payment_log.message_deposit_confirmed', [
                'amount' => $deposit->formattedCreditedAmount() ?? $deposit->formattedAmount(),
            ]),
        );
    }

    public function depositRejected(Deposit $deposit, string $source = PaymentStatusLog::SOURCE_APP): PaymentStatusLog
    {
        return $this->logDepositStatusChange(
            $deposit,
            $source,
            'status_change',
            Deposit::STATUS_PENDING,
            Deposit::STATUS_REJECTED,
            'processed',
            __('coin.payment_log.title_deposit_rejected'),
            PaymentStatusReason::depositMessage($deposit),
        );
    }

    public function withdrawalCreated(Withdrawal $withdrawal): PaymentStatusLog
    {
        return $this->logWithdrawalStatusChange(
            $withdrawal,
            PaymentStatusLog::SOURCE_APP,
            'created',
            null,
            Withdrawal::STATUS_PENDING,
            'info',
            __('coin.payment_log.title_withdrawal_created'),
            __('coin.payment_log.message_withdrawal_created', [
                'amount' => $withdrawal->formattedAmount(),
            ]),
        );
    }

    public function withdrawalDispatched(Withdrawal $withdrawal, ?string $previousStatus = Withdrawal::STATUS_PENDING): PaymentStatusLog
    {
        return $this->logWithdrawalStatusChange(
            $withdrawal,
            PaymentStatusLog::SOURCE_APP,
            'gateway_dispatch',
            $previousStatus,
            Withdrawal::STATUS_PROCESSING,
            'info',
            __('coin.payment_log.title_withdrawal_dispatched'),
            __('coin.payment_log.message_withdrawal_dispatched', [
                'gateway_id' => $withdrawal->gateway_request_id ?? '—',
            ]),
            payload: [
                'gateway_request_id' => $withdrawal->gateway_request_id,
                'payout_address' => $withdrawal->payout_address,
            ],
        );
    }

    public function withdrawalPaid(Withdrawal $withdrawal, string $source = PaymentStatusLog::SOURCE_APP): PaymentStatusLog
    {
        return $this->logWithdrawalStatusChange(
            $withdrawal,
            $source,
            'status_change',
            Withdrawal::STATUS_PROCESSING,
            Withdrawal::STATUS_PAID,
            'processed',
            __('coin.payment_log.title_payout_paid'),
            __('coin.payment_log.message_payout_paid', [
                'txid' => $withdrawal->txid ?? '—',
            ]),
            gatewayState: $withdrawal->gateway_state,
        );
    }

    public function withdrawalFailed(Withdrawal $withdrawal, string $source = PaymentStatusLog::SOURCE_APP): PaymentStatusLog
    {
        $gatewayResult = null;
        if (is_string($withdrawal->gateway_poll_summary) && str_contains($withdrawal->gateway_poll_summary, 'result=')) {
            preg_match('/result=([^\s·]+)/', $withdrawal->gateway_poll_summary, $matches);
            $gatewayResult = $matches[1] ?? null;
        }

        return $this->logWithdrawalStatusChange(
            $withdrawal,
            $source,
            'status_change',
            Withdrawal::STATUS_PROCESSING,
            Withdrawal::STATUS_REJECTED,
            'failed',
            __('coin.payment_log.title_gateway_failed'),
            PaymentStatusReason::withdrawalMessage($withdrawal),
            gatewayState: $withdrawal->gateway_state,
            gatewayResult: $gatewayResult,
        );
    }

    public function withdrawalAdminStatusChange(
        Withdrawal $withdrawal,
        ?string $previousStatus,
        string $newStatus,
    ): PaymentStatusLog {
        $result = $newStatus === Withdrawal::STATUS_REJECTED ? 'failed' : 'info';

        return $this->logWithdrawalStatusChange(
            $withdrawal,
            PaymentStatusLog::SOURCE_ADMIN,
            'status_change',
            $previousStatus,
            $newStatus,
            $result,
            __('coin.payment_log.title_admin_status_change'),
            PaymentStatusDecoder::transitionLabel($previousStatus, $newStatus, 'withdrawal'),
        );
    }
}

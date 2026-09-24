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

        [$result] = PaymentStatusDecoder::splitProcessingResult($log->processing_result);

        if ($this->shouldSkipWebhookJournalImport($log, $result, $force)) {
            return null;
        }

        $decoded = PaymentStatusDecoder::decodeWebhookMessage(
            (string) $log->processing_result,
            is_array($log->payload) ? $log->payload : null,
        );

        $entityType = $log->deposit_id !== null ? 'deposit' : ($log->withdrawal_id !== null ? 'withdrawal' : null);

        if ($entityType === null) {
            return PaymentStatusLog::query()->create([
                'entity_type' => 'unknown',
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
        $statusFields = $this->webhookJournalStatusFields($log, $result, $deposit, $withdrawal);

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
            'previous_status' => $statusFields['previous_status'],
            'new_status' => $statusFields['new_status'],
            'gateway' => $log->gateway,
            'gateway_state' => $decoded['gateway_state'],
            'gateway_result' => $decoded['gateway_result'],
            'status_reason' => $statusFields['status_reason'],
            'result' => $result,
            'title' => $decoded['title'],
            'message' => $decoded['message'],
            'payload' => $payload,
            'created_at' => $log->processed_at ?? $log->created_at,
        ]);
    }

    private function shouldSkipWebhookJournalImport(PaymentWebhookLog $log, ?string $result, bool $force): bool
    {
        if ($result === PaymentWebhookLog::RESULT_PROCESSED && ($log->deposit_id || $log->withdrawal_id)) {
            return true;
        }

        if ($result !== PaymentWebhookLog::RESULT_IGNORED) {
            return false;
        }

        [, $message] = PaymentStatusDecoder::splitProcessingResult($log->processing_result);

        if ($log->event_type === 'payout_poll') {
            return true;
        }

        return str_contains($message, 'Awaiting payout confirmation')
            || str_contains($message, 'Awaiting confirmations')
            || str_contains($message, 'Awaiting payout confirmations');
    }

    /** @return array{previous_status: ?string, new_status: ?string, status_reason: ?string} */
    private function webhookJournalStatusFields(
        PaymentWebhookLog $log,
        ?string $result,
        ?Deposit $deposit,
        ?Withdrawal $withdrawal,
    ): array {
        if ($result !== PaymentWebhookLog::RESULT_PROCESSED) {
            return [
                'previous_status' => null,
                'new_status' => null,
                'status_reason' => null,
            ];
        }

        [, $message] = PaymentStatusDecoder::splitProcessingResult($log->processing_result);

        $terminal = $this->terminalWebhookStatusFields($message, $deposit, $withdrawal);
        if ($terminal !== null) {
            return $terminal;
        }

        return [
            'previous_status' => null,
            'new_status' => null,
            'status_reason' => null,
        ];
    }

    /** @return array{previous_status: ?string, new_status: ?string, status_reason: ?string}|null */
    private function terminalWebhookStatusFields(
        string $message,
        ?Deposit $deposit,
        ?Withdrawal $withdrawal,
    ): ?array {
        if ($withdrawal !== null) {
            if (str_contains($message, 'Gateway reported failed payout')) {
                return [
                    'previous_status' => Withdrawal::STATUS_PROCESSING,
                    'new_status' => Withdrawal::STATUS_REJECTED,
                    'status_reason' => PaymentStatusReason::WITHDRAWAL_GATEWAY_FAILED,
                ];
            }

            if (str_contains($message, 'Rejected stale MOCK')) {
                return [
                    'previous_status' => Withdrawal::STATUS_PROCESSING,
                    'new_status' => Withdrawal::STATUS_REJECTED,
                    'status_reason' => PaymentStatusReason::WITHDRAWAL_MOCK_GATEWAY,
                ];
            }

            if (str_contains($message, 'Rejected after repeated CCAPI poll errors')) {
                return [
                    'previous_status' => Withdrawal::STATUS_PROCESSING,
                    'new_status' => Withdrawal::STATUS_REJECTED,
                    'status_reason' => PaymentStatusReason::WITHDRAWAL_POLL_STUCK,
                ];
            }

            if (str_contains($message, 'Marked paid')) {
                return [
                    'previous_status' => Withdrawal::STATUS_PROCESSING,
                    'new_status' => Withdrawal::STATUS_PAID,
                    'status_reason' => null,
                ];
            }
        }

        if ($deposit !== null) {
            if (str_contains($message, 'Deposit confirmed')) {
                return [
                    'previous_status' => Deposit::STATUS_PENDING,
                    'new_status' => Deposit::STATUS_CONFIRMED,
                    'status_reason' => null,
                ];
            }

            if (str_contains($message, 'Deposit rejected')) {
                return [
                    'previous_status' => Deposit::STATUS_PENDING,
                    'new_status' => Deposit::STATUS_REJECTED,
                    'status_reason' => $deposit->status_reason,
                ];
            }
        }

        return null;
    }

    public function repairExistingRow(PaymentStatusLog $log): bool
    {
        $payload = is_array($log->payload) ? $log->payload : [];
        $webhookId = $payload['webhook_log_id'] ?? null;

        if (is_numeric($webhookId)) {
            $webhook = PaymentWebhookLog::query()->find((int) $webhookId);

            if ($webhook !== null && filled($webhook->processing_result)) {
                return $this->refreshRowFromWebhook($log, $webhook);
            }
        }

        return $this->repairAppRow($log);
    }

    private function refreshRowFromWebhook(PaymentStatusLog $log, PaymentWebhookLog $webhook): bool
    {
        $decoded = PaymentStatusDecoder::decodeWebhookMessage(
            (string) $webhook->processing_result,
            is_array($webhook->payload) ? $webhook->payload : null,
        );

        [$result] = PaymentStatusDecoder::splitProcessingResult($webhook->processing_result);

        $deposit = $webhook->deposit_id ? Deposit::query()->find($webhook->deposit_id) : null;
        $withdrawal = $webhook->withdrawal_id ? Withdrawal::query()->find($webhook->withdrawal_id) : null;
        $statusFields = $this->webhookJournalStatusFields($webhook, $result, $deposit, $withdrawal);

        $log->forceFill([
            'title' => $decoded['title'],
            'message' => $decoded['message'],
            'gateway_state' => $decoded['gateway_state'],
            'gateway_result' => $decoded['gateway_result'],
            'result' => $result,
            'previous_status' => $statusFields['previous_status'],
            'new_status' => $statusFields['new_status'],
            'status_reason' => $statusFields['status_reason'],
            'created_at' => $webhook->processed_at ?? $webhook->created_at,
        ])->save();

        return true;
    }

    private function repairAppRow(PaymentStatusLog $log): bool
    {
        $updates = [];

        if ($log->event_type === 'created') {
            $updates = array_merge($updates, [
                'status_reason' => null,
                'gateway_state' => null,
                'gateway_result' => null,
            ]);

            if ($log->entity_type === 'withdrawal' && $log->withdrawal_id) {
                $withdrawal = Withdrawal::query()->find($log->withdrawal_id);
                if ($withdrawal !== null) {
                    $updates['title'] = __('coin.payment_log.title_withdrawal_created');
                    $updates['message'] = __('coin.payment_log.message_withdrawal_created', [
                        'amount' => $withdrawal->formattedAmount(),
                    ]);
                    $updates['new_status'] = Withdrawal::STATUS_PENDING;
                    $updates['previous_status'] = null;
                }
            }

            if ($log->entity_type === 'deposit' && $log->deposit_id) {
                $deposit = Deposit::query()->find($log->deposit_id);
                if ($deposit !== null) {
                    $updates['title'] = __('coin.payment_log.title_deposit_created');
                    $updates['message'] = __('coin.payment_log.message_deposit_created', [
                        'amount' => $deposit->formattedAmount(),
                    ]);
                    $updates['new_status'] = Deposit::STATUS_PENDING;
                    $updates['previous_status'] = null;
                }
            }
        }

        if ($log->source === PaymentStatusLog::SOURCE_APP && $log->event_type === 'gateway_dispatch' && $log->withdrawal_id) {
            $withdrawal = Withdrawal::query()->find($log->withdrawal_id);
            if ($withdrawal !== null) {
                $updates['title'] = __('coin.payment_log.title_withdrawal_dispatched');
                $updates['message'] = __('coin.payment_log.message_withdrawal_dispatched', [
                    'gateway_id' => $withdrawal->gateway_request_id ?? '—',
                ]);
                $updates['previous_status'] = Withdrawal::STATUS_PENDING;
                $updates['new_status'] = Withdrawal::STATUS_PROCESSING;
                $updates['status_reason'] = null;
            }
        }

        if ($log->source === PaymentStatusLog::SOURCE_APP && $log->event_type === 'status_change') {
            if ($log->withdrawal_id) {
                $withdrawal = Withdrawal::query()->find($log->withdrawal_id);
                if ($withdrawal !== null) {
                    $updates = array_merge($updates, $this->withdrawalAppStatusChangeCopy($log, $withdrawal));
                }
            }

            if ($log->deposit_id) {
                $deposit = Deposit::query()->find($log->deposit_id);
                if ($deposit !== null) {
                    $updates = array_merge($updates, $this->depositAppStatusChangeCopy($log, $deposit));
                }
            }
        }

        if ($updates === []) {
            return false;
        }

        $log->forceFill($updates)->save();

        return true;
    }

    /** @return array<string, mixed> */
    private function withdrawalAppStatusChangeCopy(PaymentStatusLog $log, Withdrawal $withdrawal): array
    {
        if ($log->new_status === Withdrawal::STATUS_PAID) {
            return [
                'title' => __('coin.payment_log.title_payout_paid'),
                'message' => __('coin.payment_log.message_payout_paid', [
                    'txid' => $withdrawal->txid ?? '—',
                ]),
                'status_reason' => null,
            ];
        }

        if ($log->new_status === Withdrawal::STATUS_REJECTED) {
            return [
                'title' => __('coin.payment_log.title_gateway_failed'),
                'message' => PaymentStatusReason::withdrawalMessage($withdrawal),
                'status_reason' => $withdrawal->status_reason,
            ];
        }

        return [
            'message' => PaymentStatusDecoder::transitionLabel(
                $log->previous_status,
                $log->new_status,
                'withdrawal',
            ),
        ];
    }

    /** @return array<string, mixed> */
    private function depositAppStatusChangeCopy(PaymentStatusLog $log, Deposit $deposit): array
    {
        if ($log->new_status === Deposit::STATUS_CONFIRMED) {
            return [
                'title' => __('coin.payment_log.title_deposit_confirmed'),
                'message' => __('coin.payment_log.message_deposit_confirmed', [
                    'amount' => $deposit->formattedCreditedAmount() ?? $deposit->formattedAmount(),
                ]),
                'status_reason' => null,
            ];
        }

        if ($log->new_status === Deposit::STATUS_REJECTED) {
            return [
                'title' => __('coin.payment_log.title_deposit_rejected'),
                'message' => PaymentStatusReason::depositMessage($deposit),
                'status_reason' => $deposit->status_reason,
            ];
        }

        return [
            'message' => PaymentStatusDecoder::transitionLabel(
                $log->previous_status,
                $log->new_status,
                'deposit',
            ),
        ];
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

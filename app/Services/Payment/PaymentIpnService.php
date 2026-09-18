<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\Payment\Dtos\VerifiedIpnEvent;
use App\Services\WithdrawalService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentIpnService
{
    public function __construct(
        private readonly DepositService $deposits,
        private readonly WithdrawalService $withdrawals,
    ) {}

    public function handle(VerifiedIpnEvent $event): PaymentWebhookLog
    {
        $log = PaymentWebhookLog::query()->create([
            'gateway' => (string) config('coin.payments.driver', 'mock'),
            'event_type' => $event->type,
            'payload' => $event->raw,
            'signature_valid' => true,
            'idempotency_key' => $event->idempotencyKey(),
        ]);

        if ($this->wasAlreadyProcessed($event)) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Duplicate IPN ignored.');
        }

        try {
            $result = DB::transaction(function () use ($event, $log) {
                if ($event->isIncomingPayment()) {
                    return $this->handleIncomingPayment($event, $log);
                }

                if ($event->isOutgoingPayment()) {
                    return $this->handleOutgoingPayment($event, $log);
                }

                return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Unsupported IPN type.');
            });
        } catch (RuntimeException $exception) {
            return $this->finish($log, PaymentWebhookLog::RESULT_FAILED, $exception->getMessage());
        }

        return $result;
    }

    private function handleIncomingPayment(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        $minConfirmations = (int) config('coin.payments.ccapi.min_confirmations', 1);

        if ($event->confirmation < $minConfirmations) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Awaiting confirmations.');
        }

        $deposit = $this->resolveDeposit($event);

        if ($deposit === null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Deposit not found for IPN label.');
        }

        $log->update(['deposit_id' => $deposit->id]);

        if ($deposit->status === Deposit::STATUS_CONFIRMED) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Deposit already confirmed.');
        }

        if ($deposit->status !== Deposit::STATUS_PENDING) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Deposit is not pending.');
        }

        $deposit->update([
            'txid' => $event->txid,
            'received_amount' => $event->amount,
        ]);

        $this->deposits->confirm($deposit->fresh(), null);

        return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Deposit confirmed from IPN.');
    }

    private function handleOutgoingPayment(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        $minConfirmations = (int) config('coin.payments.ccapi.min_confirmations', 1);

        if ($event->confirmation < $minConfirmations) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Awaiting payout confirmations.');
        }

        $withdrawal = $this->resolveWithdrawal($event);

        if ($withdrawal === null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Withdrawal not found for IPN label.');
        }

        $log->update(['withdrawal_id' => $withdrawal->id]);

        if ($withdrawal->status === Withdrawal::STATUS_PAID) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Withdrawal already paid.');
        }

        if ($withdrawal->status !== Withdrawal::STATUS_PROCESSING) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Withdrawal is not processing.');
        }

        $this->withdrawals->markPaidFromGateway(
            $withdrawal->fresh(),
            $event->txid,
            (string) $event->confirmation,
        );

        return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Withdrawal marked paid from IPN.');
    }

    private function resolveDeposit(VerifiedIpnEvent $event): ?Deposit
    {
        $reference = $this->parseReference($event->label, 'deposit');

        if ($reference !== null) {
            return Deposit::query()->find($reference);
        }

        if ($event->gatewayRequestId !== null) {
            return Deposit::query()
                ->where('gateway_uniq_id', $event->gatewayRequestId)
                ->first();
        }

        return null;
    }

    private function resolveWithdrawal(VerifiedIpnEvent $event): ?Withdrawal
    {
        $reference = $this->parseReference($event->label, 'withdrawal');

        if ($reference !== null) {
            return Withdrawal::query()->where('reference', $reference)->first();
        }

        if ($event->gatewayRequestId !== null) {
            return Withdrawal::query()
                ->where('gateway_request_id', $event->gatewayRequestId)
                ->first();
        }

        return null;
    }

    private function parseReference(?string $label, string $prefix): ?string
    {
        if ($label === null || $label === '') {
            return null;
        }

        $expectedPrefix = $prefix.':';

        if (! str_starts_with($label, $expectedPrefix)) {
            return null;
        }

        $value = substr($label, strlen($expectedPrefix));

        return $value !== '' ? $value : null;
    }

    private function wasAlreadyProcessed(VerifiedIpnEvent $event): bool
    {
        return PaymentWebhookLog::query()
            ->where('idempotency_key', $event->idempotencyKey())
            ->where('processing_result', PaymentWebhookLog::RESULT_PROCESSED)
            ->exists();
    }

    private function finish(PaymentWebhookLog $log, string $result, string $message): PaymentWebhookLog
    {
        $log->update([
            'processing_result' => PaymentWebhookLog::formatProcessingResult($result, $message),
            'processed_at' => now(),
        ]);

        return $log->fresh();
    }
}

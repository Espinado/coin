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
        try {
            return DB::transaction(function () use ($event) {
                if ($this->wasAlreadyProcessed($event)) {
                    return $this->createAuditLog(
                        $event,
                        PaymentWebhookLog::RESULT_DUPLICATE,
                        'Duplicate IPN ignored.',
                    );
                }

                $log = PaymentWebhookLog::query()->create([
                    'gateway' => (string) config('coin.payments.driver', 'mock'),
                    'event_type' => $event->type,
                    'payload' => $event->raw,
                    'signature_valid' => true,
                    'idempotency_key' => $event->idempotencyKey(),
                ]);

                if ($event->isIncomingPayment()) {
                    return $this->handleIncomingPayment($event, $log);
                }

                if ($event->isOutgoingPayment()) {
                    return $this->handleOutgoingPayment($event, $log);
                }

                return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Unsupported IPN type.');
            });
        } catch (RuntimeException $exception) {
            return $this->createAuditLog(
                $event,
                PaymentWebhookLog::RESULT_FAILED,
                $exception->getMessage(),
            );
        }
    }

    private function handleIncomingPayment(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        $minConfirmations = (int) config('coin.payments.ccapi.min_confirmations', 1);

        if ($event->confirmation < $minConfirmations) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Awaiting confirmations.');
        }

        $depositReference = $this->resolveDepositReference($event);

        if ($depositReference === null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Deposit not found for IPN label.');
        }

        $deposit = Deposit::query()
            ->whereKey($depositReference)
            ->lockForUpdate()
            ->first();

        if ($deposit === null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Deposit not found for IPN label.');
        }

        $log->update(['deposit_id' => $deposit->id]);

        if ($this->wasAlreadyProcessed($event)) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Duplicate IPN ignored.');
        }

        if ($deposit->status === Deposit::STATUS_CONFIRMED) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Deposit already confirmed.');
        }

        if ($deposit->status !== Deposit::STATUS_PENDING) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Deposit is not pending.');
        }

        $validationError = $this->validateDepositAgainstIpn($event, $deposit);

        if ($validationError !== null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, $validationError);
        }

        $deposit->update([
            'txid' => $event->txid,
            'received_amount' => $event->amount,
        ]);

        if (! $this->receivedAmountMatchesDeposit($event, $deposit)) {
            $this->deposits->reject($deposit, null);

            return $this->finish(
                $log,
                PaymentWebhookLog::RESULT_IGNORED,
                sprintf(
                    'Amount mismatch: received %s, expected %s %s.',
                    number_format((float) ($event->amount ?? 0), 6, '.', ''),
                    number_format((float) $deposit->amount, 2, '.', ''),
                    strtoupper((string) $deposit->currency),
                ),
            );
        }

        $this->deposits->confirm($deposit, null);

        return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Deposit confirmed from IPN.');
    }

    public static function receivedAmountMatchesDepositAmount(
        ?float $received,
        float $expectedAmount,
        ?float $tolerance = null,
    ): bool {
        if ($received === null || $received <= 0) {
            return false;
        }

        $tolerance ??= max(0, (float) config('coin.payments.ccapi.amount_tolerance', 0));

        return abs($received - $expectedAmount) <= $tolerance;
    }

    private function receivedAmountMatchesDeposit(VerifiedIpnEvent $event, Deposit $deposit): bool
    {
        return self::receivedAmountMatchesDepositAmount(
            $event->amount,
            (float) $deposit->amount,
        );
    }

    private function validateDepositAgainstIpn(VerifiedIpnEvent $event, Deposit $deposit): ?string
    {
        if ($deposit->method === 'mock') {
            if (app()->environment(['local', 'testing'])) {
                return null;
            }

            return 'Mock deposits cannot be confirmed from webhook in production.';
        }

        if ($deposit->method !== 'ccapi') {
            return 'Deposit method is not ccapi.';
        }

        $expectedAddress = trim((string) $deposit->payment_address);
        $receivedAddress = trim((string) ($event->to ?? ''));

        if ($expectedAddress !== '' && $receivedAddress !== ''
            && strcasecmp($expectedAddress, $receivedAddress) !== 0) {
            return 'Payment address mismatch.';
        }

        $expectedCurrency = strtoupper((string) $deposit->currency);
        $incomingAsset = strtoupper(trim((string) ($event->token ?? '')));

        if ($incomingAsset === '') {
            $incomingAsset = strtoupper(trim((string) ($event->currency ?? '')));
        }

        if ($incomingAsset !== '' && $expectedCurrency !== $incomingAsset) {
            return 'Currency/token mismatch.';
        }

        return null;
    }

    private function handleOutgoingPayment(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        $minConfirmations = (int) config('coin.payments.ccapi.min_confirmations', 1);

        if ($event->confirmation < $minConfirmations) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Awaiting payout confirmations.');
        }

        $withdrawalReference = $this->resolveWithdrawalReference($event);

        if ($withdrawalReference === null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Withdrawal not found for IPN label.');
        }

        $withdrawal = Withdrawal::query()
            ->whereKey($withdrawalReference)
            ->lockForUpdate()
            ->first();

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
            $withdrawal,
            $event->txid,
            (string) $event->confirmation,
        );

        return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Withdrawal marked paid from IPN.');
    }

    private function resolveDepositReference(VerifiedIpnEvent $event): ?int
    {
        $reference = $this->parseReference($event->label, 'deposit');

        if ($reference !== null) {
            return (int) $reference;
        }

        if ($event->gatewayRequestId !== null) {
            $depositId = Deposit::query()
                ->where('gateway_uniq_id', $event->gatewayRequestId)
                ->value('id');

            return $depositId !== null ? (int) $depositId : null;
        }

        return null;
    }

    private function resolveWithdrawalReference(VerifiedIpnEvent $event): ?int
    {
        $reference = $this->parseReference($event->label, 'withdrawal');

        if ($reference !== null) {
            return Withdrawal::query()
                ->where('reference', $reference)
                ->value('id');
        }

        if ($event->gatewayRequestId !== null) {
            $withdrawalId = Withdrawal::query()
                ->where('gateway_request_id', $event->gatewayRequestId)
                ->value('id');

            return $withdrawalId !== null ? (int) $withdrawalId : null;
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
            ->where('processing_result', 'like', PaymentWebhookLog::RESULT_PROCESSED.':%')
            ->exists();
    }

    private function createAuditLog(VerifiedIpnEvent $event, string $result, string $message): PaymentWebhookLog
    {
        return PaymentWebhookLog::query()->create([
            'gateway' => (string) config('coin.payments.driver', 'mock'),
            'event_type' => $event->type,
            'payload' => $event->raw,
            'signature_valid' => true,
            'idempotency_key' => $event->idempotencyKey(),
            'processing_result' => PaymentWebhookLog::formatProcessingResult($result, $message),
            'processed_at' => now(),
        ]);
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

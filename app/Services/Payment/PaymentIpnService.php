<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Support\PaymentStatusReason;
use App\Services\DepositService;
use App\Services\Payment\Dtos\VerifiedIpnEvent;
use App\Services\WithdrawalService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                    if ($event->isIncomingPayment()) {
                        $this->rejectPendingDepositIfTxidAlreadyCredited($event);
                    }

                    return $this->finishDuplicate($event, 'Duplicate IPN ignored.');
                }

                $log = $this->resolveLogForProcessing($event);

                if ($log === null) {
                    return $this->finishDuplicate($event, 'Duplicate IPN ignored (concurrent).');
                }

                return $this->dispatchPaymentHandling($event, $log);
            });
        } catch (PaymentIpnRetryableException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            return $this->finishOrCreateFailed($event, $exception->getMessage());
        }
    }

    private function handleIncomingPayment(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        $depositReference = $this->resolveDepositReference($event);

        if ($depositReference !== null) {
            $log->update(['deposit_id' => $depositReference]);
        }

        $minConfirmations = (int) config('coin.payments.ccapi.min_confirmations', 1);

        if ($event->confirmation < $minConfirmations) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Awaiting confirmations.');
        }

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

        if ($this->wasAlreadyProcessed($event)) {
            return $this->finishDuplicate($event, 'Duplicate IPN ignored.', $deposit);
        }

        if ($deposit->status === Deposit::STATUS_CONFIRMED) {
            $this->restoreProcessedWebhookDepositLink($deposit);

            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Deposit already confirmed.');
        }

        if ($deposit->status !== Deposit::STATUS_PENDING) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Deposit is not pending.');
        }

        $validationError = self::validateDepositAgainstIpn($event, $deposit);

        if ($validationError !== null) {
            $rejectReason = PaymentStatusReason::depositReasonFromValidation($validationError);

            if ($rejectReason !== null) {
                $this->deposits->reject($deposit, null, $rejectReason, logSource: PaymentStatusLog::SOURCE_IPN);

                return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Deposit rejected: '.$validationError);
            }

            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, $validationError);
        }

        if (! $this->receivedAmountMatchesDeposit($event, $deposit)) {
            $this->deposits->reject(
                $deposit,
                null,
                PaymentStatusReason::DEPOSIT_AMOUNT_MISMATCH,
                (float) ($event->amount ?? 0),
                PaymentStatusLog::SOURCE_IPN,
            );

            return $this->finish(
                $log,
                PaymentWebhookLog::RESULT_PROCESSED,
                sprintf(
                    'Amount mismatch: received %s, expected %s %s.',
                    number_format((float) ($event->amount ?? 0), 6, '.', ''),
                    number_format((float) $deposit->amount, 2, '.', ''),
                    strtoupper((string) $deposit->currency),
                ),
            );
        }

        $duplicateResponse = $this->rejectIfTxidAlreadyUsed($event, $deposit, $log);

        if ($duplicateResponse !== null) {
            return $duplicateResponse;
        }

        $assignResponse = $this->assignDepositReceiptFromIpn($event, $deposit, $log);

        if ($assignResponse !== null) {
            return $assignResponse;
        }

        $this->deposits->confirm($deposit, null, PaymentStatusLog::SOURCE_IPN);

        return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Deposit confirmed from IPN.');
    }

    public static function receivedAmountMatchesDepositAmount(
        ?float $received,
        float $expectedAmount,
        ?float $tolerance = null,
        ?string $currency = null,
    ): bool {
        if ($received === null || $received <= 0) {
            return false;
        }

        $tolerance ??= max(0, (float) config('coin.payments.ccapi.amount_tolerance', 0));
        $decimals = strtoupper((string) ($currency ?? 'USDT')) === 'BTC' ? 8 : 2;
        $normalizedExpected = (float) number_format($expectedAmount, $decimals, '.', '');
        $normalizedReceived = (float) number_format($received, $decimals, '.', '');

        return abs($normalizedReceived - $normalizedExpected) <= $tolerance;
    }

    private function receivedAmountMatchesDeposit(VerifiedIpnEvent $event, Deposit $deposit): bool
    {
        return self::receivedAmountMatchesDepositAmount(
            $event->amount,
            (float) $deposit->amount,
            currency: (string) $deposit->currency,
        );
    }

    public static function validateDepositAgainstIpn(VerifiedIpnEvent $event, Deposit $deposit): ?string
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

        if ($deposit->expires_at !== null && $deposit->expires_at->isPast()) {
            return 'Deposit payment window expired.';
        }

        $expectedLabel = Deposit::gatewayUniqId($deposit->id);

        if ($deposit->gateway_uniq_id !== null && $deposit->gateway_uniq_id !== $expectedLabel) {
            return 'Deposit gateway reference mismatch.';
        }

        if ($event->label !== null && $event->label !== '' && $event->label !== $expectedLabel) {
            return 'Deposit label mismatch.';
        }

        if ($event->gatewayRequestId !== null && $event->gatewayRequestId !== '' && $event->gatewayRequestId !== $expectedLabel) {
            return 'Gateway request id mismatch.';
        }

        $expectedChain = self::chainForGatewayNetwork($deposit->gateway_network);

        if ($expectedChain !== '' && strcasecmp(trim($event->chain), $expectedChain) !== 0) {
            return 'Blockchain network mismatch.';
        }

        $expectedAddress = trim((string) $deposit->payment_address);
        $receivedAddress = trim((string) ($event->to ?? ''));

        if ($expectedAddress === '') {
            return 'Deposit has no payment address.';
        }

        if ($receivedAddress === '') {
            return 'IPN missing payment address.';
        }

        if (strcasecmp($expectedAddress, $receivedAddress) !== 0) {
            return 'Payment address mismatch.';
        }

        if ($event->txid === null || trim($event->txid) === '') {
            return 'IPN missing transaction id.';
        }

        $expectedCurrency = strtoupper((string) $deposit->currency);
        $networkConfig = config('coin.payments.ccapi.networks.'.$expectedCurrency);

        if (! is_array($networkConfig)) {
            return 'Unsupported deposit currency.';
        }

        $expectedToken = strtoupper(trim((string) ($networkConfig['token'] ?? '')));

        if ($expectedToken !== '') {
            $incomingToken = strtoupper(trim((string) ($event->token ?? '')));

            if ($incomingToken !== $expectedToken) {
                return 'Currency/token mismatch.';
            }
        } elseif (strcasecmp(trim((string) ($event->currency ?? '')), $expectedCurrency) !== 0) {
            return 'Currency mismatch.';
        }

        return null;
    }

    public static function validateWithdrawalAgainstIpn(VerifiedIpnEvent $event, Withdrawal $withdrawal): ?string
    {
        $expectedLabel = Withdrawal::gatewayUniqId($withdrawal->reference);

        if ($event->label !== null && $event->label !== '' && $event->label !== $expectedLabel) {
            return 'Withdrawal label mismatch.';
        }

        if ($withdrawal->gateway_request_id !== null
            && $event->gatewayRequestId !== null
            && $event->gatewayRequestId !== ''
            && $event->gatewayRequestId !== (string) $withdrawal->gateway_request_id) {
            return 'Withdrawal gateway request id mismatch.';
        }

        $expectedAddress = trim((string) $withdrawal->payout_address);
        $receivedAddress = trim((string) ($event->to ?? ''));

        if ($expectedAddress === '') {
            return 'Withdrawal has no payout address.';
        }

        if ($receivedAddress === '') {
            return 'IPN missing payout address.';
        }

        if (strcasecmp($expectedAddress, $receivedAddress) !== 0) {
            return 'Payout address mismatch.';
        }

        if ($event->txid === null || trim($event->txid) === '') {
            return 'IPN missing transaction id.';
        }

        $expectedCurrency = strtoupper((string) $withdrawal->currency);
        $networkConfig = config('coin.payments.ccapi.networks.'.$expectedCurrency);

        if (is_array($networkConfig)) {
            $expectedToken = strtoupper(trim((string) ($networkConfig['token'] ?? '')));

            if ($expectedToken !== '') {
                $incomingToken = strtoupper(trim((string) ($event->token ?? '')));

                if ($incomingToken !== $expectedToken) {
                    return 'Payout currency/token mismatch.';
                }
            } elseif (strcasecmp(trim((string) ($event->currency ?? '')), $expectedCurrency) !== 0) {
                return 'Payout currency mismatch.';
            }
        }

        return null;
    }

    private static function chainForGatewayNetwork(?string $network): string
    {
        return match ($network) {
            'btc' => 'bitcoin',
            'eth' => 'ethereum',
            'bnb' => 'bsc',
            'trx', null, '' => 'tron',
            default => '',
        };
    }

    private function handleOutgoingPayment(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        $withdrawalReference = $this->resolveWithdrawalReference($event);

        if ($withdrawalReference !== null) {
            $log->update(['withdrawal_id' => $withdrawalReference]);
        }

        $minConfirmations = (int) config('coin.payments.ccapi.min_confirmations', 1);

        if ($event->confirmation < $minConfirmations) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Awaiting payout confirmations.');
        }

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

        if ($withdrawal->status === Withdrawal::STATUS_PAID) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, 'Withdrawal already paid.');
        }

        if ($withdrawal->status !== Withdrawal::STATUS_PROCESSING) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Withdrawal is not processing.');
        }

        $validationError = self::validateWithdrawalAgainstIpn($event, $withdrawal);

        if ($validationError !== null) {
            return $this->rejectWithdrawalFromIpnMismatch($withdrawal, $log, $validationError, $event);
        }

        if (! self::receivedAmountMatchesDepositAmount(
            $event->amount,
            (float) $withdrawal->amount,
            currency: (string) $withdrawal->currency,
        )) {
            return $this->rejectWithdrawalFromIpnMismatch(
                $withdrawal,
                $log,
                sprintf(
                    'Payout amount mismatch: received %s, expected %s %s.',
                    number_format((float) ($event->amount ?? 0), 6, '.', ''),
                    number_format((float) $withdrawal->amount, strtoupper((string) $withdrawal->currency) === 'BTC' ? 8 : 2, '.', ''),
                    strtoupper((string) $withdrawal->currency),
                ),
                $event,
            );
        }

        $duplicateResponse = $this->rejectIfWithdrawalTxidAlreadyPaid($event, $withdrawal, $log);

        if ($duplicateResponse !== null) {
            return $duplicateResponse;
        }

        $this->withdrawals->markPaidFromGateway(
            $withdrawal,
            $event->txid,
            (string) $event->confirmation,
            PaymentStatusLog::SOURCE_IPN,
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
            ->where('gateway', (string) config('coin.payments.driver', 'mock'))
            ->where('idempotency_key', $event->idempotencyKey())
            ->where('processing_result', 'like', PaymentWebhookLog::RESULT_PROCESSED.':%')
            ->exists();
    }

    private function findLogByIdempotency(VerifiedIpnEvent $event): ?PaymentWebhookLog
    {
        return PaymentWebhookLog::query()
            ->where('gateway', (string) config('coin.payments.driver', 'mock'))
            ->where('idempotency_key', $event->idempotencyKey())
            ->first();
    }

    private function resolveLogForProcessing(VerifiedIpnEvent $event): ?PaymentWebhookLog
    {
        $existing = $this->findLogByIdempotency($event);

        if ($existing !== null) {
            return $this->prepareLogForRetry($existing, $event);
        }

        try {
            return PaymentWebhookLog::query()->create([
                'gateway' => (string) config('coin.payments.driver', 'mock'),
                'event_type' => $event->type,
                'payload' => $event->raw,
                'signature_valid' => true,
                'idempotency_key' => $event->idempotencyKey(),
            ]);
        } catch (UniqueConstraintViolationException) {
            $existing = $this->findLogByIdempotency($event);

            if ($existing === null) {
                return null;
            }

            return $this->prepareLogForRetry($existing, $event);
        }
    }

    private function dispatchPaymentHandling(VerifiedIpnEvent $event, PaymentWebhookLog $log): PaymentWebhookLog
    {
        if ($event->isIncomingPayment()) {
            return $this->handleIncomingPayment($event, $log);
        }

        if ($event->isOutgoingPayment()) {
            return $this->handleOutgoingPayment($event, $log);
        }

        return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Unsupported IPN type.');
    }

    private function prepareLogForRetry(PaymentWebhookLog $log, VerifiedIpnEvent $event): ?PaymentWebhookLog
    {
        if (! $this->isRetryableLog($log)) {
            return null;
        }

        $log->update([
            'event_type' => $event->type,
            'payload' => $event->raw,
        ]);

        return $log->fresh();
    }

    private function isRetryableLog(PaymentWebhookLog $log): bool
    {
        return in_array($log->resultType(), [
            PaymentWebhookLog::RESULT_IGNORED,
            PaymentWebhookLog::RESULT_FAILED,
        ], true);
    }

    private function finishDuplicate(VerifiedIpnEvent $event, string $message, ?Deposit $deposit = null): PaymentWebhookLog
    {
        $log = $this->findLogByIdempotency($event);

        if ($log !== null) {
            if (str_starts_with((string) $log->processing_result, PaymentWebhookLog::RESULT_PROCESSED.':')) {
                $this->ensureWebhookLogLinkedToDeposit($log, $deposit);

                return $log->fresh();
            }

            $prepared = $this->prepareLogForRetry($log, $event);

            if ($prepared !== null) {
                return $this->dispatchPaymentHandling($event, $prepared);
            }

            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, $message);
        }

        if ($deposit !== null) {
            $this->restoreProcessedWebhookDepositLink($deposit);
        }

        return $this->createAuditLog($event, PaymentWebhookLog::RESULT_DUPLICATE, $message);
    }

    private function ensureWebhookLogLinkedToDeposit(PaymentWebhookLog $log, ?Deposit $deposit): void
    {
        if ($log->deposit_id !== null || $deposit === null) {
            return;
        }

        $log->update(['deposit_id' => $deposit->id]);
    }

    private function restoreProcessedWebhookDepositLink(Deposit $deposit): void
    {
        PaymentWebhookLog::query()
            ->linkedToDeposit($deposit)
            ->where('processing_result', 'like', PaymentWebhookLog::RESULT_PROCESSED.':%')
            ->whereNull('deposit_id')
            ->update(['deposit_id' => $deposit->id]);
    }

    private function rejectWithdrawalFromIpnMismatch(
        Withdrawal $withdrawal,
        PaymentWebhookLog $log,
        string $reason,
        VerifiedIpnEvent $event,
    ): PaymentWebhookLog {
        Log::warning('Withdrawal IPN rejected due to mismatch.', [
            'withdrawal_id' => $withdrawal->id,
            'reference' => $withdrawal->reference,
            'reason' => $reason,
            'txid' => $event->txid,
        ]);

        $this->withdrawals->markFailedFromGateway(
            $withdrawal,
            null,
            'IPN mismatch: '.$reason,
            PaymentStatusReason::WITHDRAWAL_IPN_MISMATCH,
            PaymentStatusLog::SOURCE_IPN,
        );

        return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Withdrawal rejected: '.$reason);
    }

    private function rejectIfWithdrawalTxidAlreadyPaid(
        VerifiedIpnEvent $event,
        Withdrawal $withdrawal,
        PaymentWebhookLog $log,
    ): ?PaymentWebhookLog {
        $duplicateWithdrawal = $this->withdrawals->findPaidWithdrawalWithTxid($event->txid, $withdrawal->id);

        if ($duplicateWithdrawal === null) {
            return null;
        }

        return $this->rejectWithdrawalFromIpnMismatch(
            $withdrawal,
            $log,
            sprintf('Transaction id already paid on withdrawal %s.', $duplicateWithdrawal->reference),
            $event,
        );
    }

    private function rejectIfTxidAlreadyUsed(
        VerifiedIpnEvent $event,
        Deposit $deposit,
        PaymentWebhookLog $log,
    ): ?PaymentWebhookLog {
        $duplicateDeposit = $this->confirmedDepositWithTxid($event->txid, $deposit->id);

        if ($duplicateDeposit === null) {
            return null;
        }

        $this->deposits->reject(
            $deposit,
            null,
            PaymentStatusReason::DEPOSIT_DUPLICATE_TXID,
            (float) ($event->amount ?? 0),
            PaymentStatusLog::SOURCE_IPN,
        );

        return $this->finish(
            $log,
            PaymentWebhookLog::RESULT_PROCESSED,
            sprintf('Deposit rejected: txid already credited on deposit #%d.', $duplicateDeposit->id),
        );
    }

    private function assignDepositReceiptFromIpn(
        VerifiedIpnEvent $event,
        Deposit $deposit,
        PaymentWebhookLog $log,
    ): ?PaymentWebhookLog {
        try {
            $deposit->update([
                'txid' => $event->txid,
                'received_amount' => $event->amount,
            ]);
        } catch (UniqueConstraintViolationException) {
            $this->deposits->reject(
                $deposit,
                null,
                PaymentStatusReason::DEPOSIT_DUPLICATE_TXID,
                (float) ($event->amount ?? 0),
                PaymentStatusLog::SOURCE_IPN,
            );

            return $this->finish(
                $log,
                PaymentWebhookLog::RESULT_PROCESSED,
                'Deposit rejected: txid already assigned to another deposit.',
            );
        }

        return null;
    }

    private function confirmedDepositWithTxid(?string $txid, int $exceptDepositId): ?Deposit
    {
        if ($txid === null || trim($txid) === '') {
            return null;
        }

        return Deposit::query()
            ->where('txid', $txid)
            ->where('status', Deposit::STATUS_CONFIRMED)
            ->whereKeyNot($exceptDepositId)
            ->first();
    }

    private function rejectPendingDepositIfTxidAlreadyCredited(VerifiedIpnEvent $event): void
    {
        $depositReference = $this->resolveDepositReference($event);

        if ($depositReference === null) {
            return;
        }

        $deposit = Deposit::query()->find($depositReference);

        if ($deposit === null || $deposit->status !== Deposit::STATUS_PENDING) {
            return;
        }

        $duplicateDeposit = $this->confirmedDepositWithTxid($event->txid, $deposit->id);

        if ($duplicateDeposit === null) {
            return;
        }

        $this->deposits->reject(
            $deposit,
            null,
            PaymentStatusReason::DEPOSIT_DUPLICATE_TXID,
            (float) ($event->amount ?? 0),
            PaymentStatusLog::SOURCE_IPN,
        );
    }

    private function finishOrCreateFailed(VerifiedIpnEvent $event, string $message): PaymentWebhookLog
    {
        try {
            $log = $this->findLogByIdempotency($event);

            if ($log !== null) {
                return $this->finish($log, PaymentWebhookLog::RESULT_FAILED, $message);
            }

            return $this->createAuditLog($event, PaymentWebhookLog::RESULT_FAILED, $message);
        } catch (UniqueConstraintViolationException) {
            return $this->finishDuplicate($event, 'Duplicate IPN ignored (concurrent).');
        } catch (\Throwable $exception) {
            throw new PaymentIpnRetryableException(
                'Failed to persist IPN failure audit log: '.$exception->getMessage(),
                0,
                $exception,
            );
        }
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

<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Support\PaymentStatusReason;
use App\Services\DepositService;
use App\Services\Payment\Dtos\VerifiedIpnEvent;
use App\Services\WithdrawalService;
use Illuminate\Database\UniqueConstraintViolationException;
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
                    return $this->finishDuplicate($event, 'Duplicate IPN ignored.');
                }

                try {
                    $log = PaymentWebhookLog::query()->create([
                        'gateway' => (string) config('coin.payments.driver', 'mock'),
                        'event_type' => $event->type,
                        'payload' => $event->raw,
                        'signature_valid' => true,
                        'idempotency_key' => $event->idempotencyKey(),
                    ]);
                } catch (UniqueConstraintViolationException) {
                    return $this->finishDuplicate($event, 'Duplicate IPN ignored (concurrent).');
                }

                if ($event->isIncomingPayment()) {
                    return $this->handleIncomingPayment($event, $log);
                }

                if ($event->isOutgoingPayment()) {
                    return $this->handleOutgoingPayment($event, $log);
                }

                return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, 'Unsupported IPN type.');
            });
        } catch (PaymentIpnRetryableException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            return $this->finishOrCreateFailed($event, $exception->getMessage());
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

        $validationError = self::validateDepositAgainstIpn($event, $deposit);

        if ($validationError !== null) {
            $rejectReason = PaymentStatusReason::depositReasonFromValidation($validationError);

            if ($rejectReason !== null) {
                $this->deposits->reject($deposit, null, $rejectReason);

                return $this->finish($log, PaymentWebhookLog::RESULT_PROCESSED, 'Deposit rejected: '.$validationError);
            }

            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, $validationError);
        }

        $deposit->update([
            'txid' => $event->txid,
            'received_amount' => $event->amount,
        ]);

        if (! $this->receivedAmountMatchesDeposit($event, $deposit)) {
            $this->deposits->reject(
                $deposit,
                null,
                PaymentStatusReason::DEPOSIT_AMOUNT_MISMATCH,
                (float) ($event->amount ?? 0),
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

        $this->deposits->confirm($deposit, null);

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

        $validationError = self::validateWithdrawalAgainstIpn($event, $withdrawal);

        if ($validationError !== null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_IGNORED, $validationError);
        }

        if (! self::receivedAmountMatchesDepositAmount(
            $event->amount,
            (float) $withdrawal->amount,
            currency: (string) $withdrawal->currency,
        )) {
            return $this->finish(
                $log,
                PaymentWebhookLog::RESULT_IGNORED,
                sprintf(
                    'Payout amount mismatch: received %s, expected %s %s.',
                    number_format((float) ($event->amount ?? 0), 6, '.', ''),
                    number_format((float) $withdrawal->amount, strtoupper((string) $withdrawal->currency) === 'BTC' ? 8 : 2, '.', ''),
                    strtoupper((string) $withdrawal->currency),
                ),
            );
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

    private function finishDuplicate(VerifiedIpnEvent $event, string $message): PaymentWebhookLog
    {
        $log = $this->findLogByIdempotency($event);

        if ($log !== null) {
            return $this->finish($log, PaymentWebhookLog::RESULT_DUPLICATE, $message);
        }

        return $this->createAuditLog($event, PaymentWebhookLog::RESULT_DUPLICATE, $message);
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

<?php

namespace App\Services\Payment;

use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Services\Payment\Dtos\PayoutStatusDto;
use App\Services\PlatformSettingsService;
use App\Services\WithdrawalService;
use Illuminate\Support\Facades\Log;

class WithdrawalPollService
{
    public function __construct(
        private readonly PlatformSettingsService $settings,
        private readonly WithdrawalService $withdrawals,
    ) {}

    /** @return array{polled: int, completed: int, failed: int, pending: int, errors: int} */
    public function pollStuckWithdrawals(): array
    {
        $stats = [
            'polled' => 0,
            'completed' => 0,
            'failed' => 0,
            'pending' => 0,
            'errors' => 0,
        ];

        if (! $this->shouldPoll()) {
            return $stats;
        }

        $gateway = app(PaymentGatewayInterface::class);

        Withdrawal::query()
            ->where('status', Withdrawal::STATUS_PROCESSING)
            ->whereNotNull('gateway_request_id')
            ->orderBy('id')
            ->each(function (Withdrawal $withdrawal) use ($gateway, &$stats): void {
                $stats['polled']++;
                $this->pollWithdrawal($withdrawal, $gateway, $stats);
            });

        if ($stats['polled'] > 0) {
            Log::info('withdrawal.poll.batch', $stats);
        }

        return $stats;
    }

    /** @param array{polled: int, completed: int, failed: int, pending: int, errors: int} $stats */
    private function pollWithdrawal(Withdrawal $withdrawal, PaymentGatewayInterface $gateway, array &$stats): void
    {
        try {
            $status = $gateway->getPayoutStatus($withdrawal->fresh());
        } catch (PaymentGatewayException $exception) {
            $stats['errors']++;
            $message = 'Poll error: '.$exception->getMessage();
            $this->touchPollMeta($withdrawal, null, $message);
            $this->recordPollLog($withdrawal, PaymentWebhookLog::RESULT_FAILED, $message, [
                'error' => $exception->getMessage(),
            ]);
            Log::warning('withdrawal.poll.error', [
                'withdrawal_id' => $withdrawal->id,
                'reference' => $withdrawal->reference,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        $summary = $this->buildSummary($status);
        $this->touchPollMeta($withdrawal, $status->state, $summary);

        if ($status->isConfirmed()) {
            if (! $this->statusMatchesWithdrawal($status, $withdrawal)) {
                $message = 'Poll confirmed but payout details mismatch.';
                $this->recordPollLog($withdrawal, PaymentWebhookLog::RESULT_IGNORED, $message, $status->raw);
                Log::warning('withdrawal.poll.mismatch', [
                    'withdrawal_id' => $withdrawal->id,
                    'reference' => $withdrawal->reference,
                ]);

                return;
            }

            $this->withdrawals->markPaidFromGateway(
                $withdrawal->fresh(),
                $status->txid,
                $status->state,
            );

            $this->recordPollLog(
                $withdrawal,
                PaymentWebhookLog::RESULT_PROCESSED,
                'Marked paid from CCAPI status poll.',
                $status->raw,
            );

            $stats['completed']++;

            Log::info('withdrawal.poll.completed', [
                'withdrawal_id' => $withdrawal->id,
                'reference' => $withdrawal->reference,
                'txid' => $status->txid,
            ]);

            return;
        }

        if ($status->isFailed()) {
            $message = 'Gateway reported failed payout (state '.$status->state.'). Funds restored.';
            $this->withdrawals->markFailedFromGateway(
                $withdrawal->fresh(),
                $status->state,
            );
            $this->recordPollLog($withdrawal, PaymentWebhookLog::RESULT_PROCESSED, $message, $status->raw);
            $stats['failed']++;

            Log::warning('withdrawal.poll.gateway_failed', [
                'withdrawal_id' => $withdrawal->id,
                'reference' => $withdrawal->reference,
                'state' => $status->state,
            ]);

            return;
        }

        $message = 'Awaiting payout confirmation (state '.$status->state.').';
        $this->recordPollLog($withdrawal, PaymentWebhookLog::RESULT_IGNORED, $message, $status->raw);
        $stats['pending']++;
    }

    private function shouldPoll(): bool
    {
        if (! config('coin.payments.ccapi.poll_stuck_withdrawals', true)) {
            return false;
        }

        return $this->settings->usesLivePaymentGateway()
            && (string) config('coin.payments.driver', 'mock') === 'ccapi';
    }

    private function buildSummary(PayoutStatusDto $status): string
    {
        $parts = ['state='.$status->state];

        if ($status->txid) {
            $parts[] = 'txid='.$status->txid;
        }

        if ($status->result) {
            $parts[] = 'result='.$status->result;
        }

        return implode(' · ', $parts);
    }

    private function statusMatchesWithdrawal(PayoutStatusDto $status, Withdrawal $withdrawal): bool
    {
        $raw = $status->raw;

        if (isset($raw['to']) && is_string($raw['to']) && $raw['to'] !== '') {
            if (strcasecmp(trim($raw['to']), trim((string) $withdrawal->payout_address)) !== 0) {
                return false;
            }
        }

        if (! isset($raw['amount']) || $raw['amount'] === '') {
            return false;
        }

        if (! PaymentIpnService::receivedAmountMatchesDepositAmount(
            (float) $raw['amount'],
            (float) $withdrawal->amount,
            currency: (string) $withdrawal->currency,
        )) {
            return false;
        }

        return $status->txid !== null && trim($status->txid) !== '';
    }

    private function touchPollMeta(Withdrawal $withdrawal, ?string $state, string $summary): void
    {
        $withdrawal->update([
            'gateway_state' => $state ?? $withdrawal->gateway_state,
            'gateway_poll_checked_at' => now(),
            'gateway_poll_summary' => PaymentWebhookLog::formatProcessingResult('poll', $summary, 500),
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function recordPollLog(Withdrawal $withdrawal, string $result, string $message, array $payload): void
    {
        PaymentWebhookLog::query()->create([
            'gateway' => (string) config('coin.payments.driver', 'mock'),
            'event_type' => 'payout_poll',
            'payload' => $payload,
            'signature_valid' => true,
            'idempotency_key' => 'poll:'.$withdrawal->id.':'.now()->format('Y-m-d-H-i-s'),
            'withdrawal_id' => $withdrawal->id,
            'processing_result' => PaymentWebhookLog::formatProcessingResult($result, $message),
            'processed_at' => now(),
        ]);
    }
}

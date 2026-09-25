<?php



namespace App\Services\Payment;



use App\Models\PaymentStatusLog;

use App\Models\PaymentWebhookLog;

use App\Models\Withdrawal;

use App\Services\Payment\Dtos\PayoutStatusDto;

use App\Services\PlatformSettingsService;

use App\Services\WithdrawalService;

use App\Support\PaymentStatusReason;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;



class WithdrawalPollService

{

    public function __construct(

        private readonly PlatformSettingsService $settings,

        private readonly WithdrawalService $withdrawals,

    ) {}



    /** @return array{polled: int, completed: int, failed: int, pending: int, errors: int, abandoned: int} */

    public function pollStuckWithdrawals(): array

    {

        $stats = [

            'polled' => 0,

            'completed' => 0,

            'failed' => 0,

            'pending' => 0,

            'errors' => 0,

            'abandoned' => 0,

            'stale' => 0,

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



    /** @param array{polled: int, completed: int, failed: int, pending: int, errors: int, abandoned: int} $stats */

    private function pollWithdrawal(Withdrawal $withdrawal, PaymentGatewayInterface $gateway, array &$stats): void

    {

        $withdrawal = $withdrawal->fresh();



        if ($this->shouldAbandonAsMockGateway($withdrawal)) {

            $this->abandonWithdrawal(

                $withdrawal,

                PaymentStatusReason::WITHDRAWAL_MOCK_GATEWAY,

                'mock_gateway_reference',

                'Rejected stale MOCK gateway reference on live CCAPI.',

                $stats,

            );



            return;

        }



        if ($this->shouldAbandonAfterRepeatedPollErrors($withdrawal)) {

            $this->abandonWithdrawal(

                $withdrawal,

                PaymentStatusReason::WITHDRAWAL_POLL_STUCK,

                'poll_stuck',

                'Rejected after repeated CCAPI poll errors.',

                $stats,

            );



            return;

        }



        try {

            $status = $gateway->getPayoutStatus($withdrawal);

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

                Log::warning('withdrawal.poll.mismatch', [

                    'withdrawal_id' => $withdrawal->id,

                    'reference' => $withdrawal->reference,

                ]);

                $this->withdrawals->markFailedFromGateway(

                    $withdrawal->fresh(),

                    $status->state,

                    $message,

                    PaymentStatusReason::WITHDRAWAL_IPN_MISMATCH,

                    PaymentStatusLog::SOURCE_POLL,

                );

                $this->recordPollLog($withdrawal, PaymentWebhookLog::RESULT_PROCESSED, $message, $status->raw);



                return;

            }



            if ($this->withdrawals->findPaidWithdrawalWithTxid($status->txid, $withdrawal->id) !== null) {

                $message = 'Poll confirmed but transaction id is already used by another withdrawal.';

                Log::warning('withdrawal.poll.duplicate_txid', [

                    'withdrawal_id' => $withdrawal->id,

                    'reference' => $withdrawal->reference,

                    'txid' => $status->txid,

                ]);

                $this->withdrawals->markFailedFromGateway(

                    $withdrawal->fresh(),

                    $status->state,

                    $message,

                    PaymentStatusReason::WITHDRAWAL_IPN_MISMATCH,

                    PaymentStatusLog::SOURCE_POLL,

                );

                $this->recordPollLog($withdrawal, PaymentWebhookLog::RESULT_PROCESSED, $message, $status->raw);



                return;

            }



            $this->withdrawals->markPaidFromGateway(

                $withdrawal->fresh(),

                $status->txid,

                $status->state,

                PaymentStatusLog::SOURCE_POLL,

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

                logSource: PaymentStatusLog::SOURCE_POLL,

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

        $this->alertIfStaleProcessing($withdrawal, $stats);

    }



    /** @param array{polled: int, completed: int, failed: int, pending: int, errors: int, abandoned: int, stale: int} $stats */
    private function alertIfStaleProcessing(Withdrawal $withdrawal, array &$stats): void
    {
        $staleHours = max(1, (int) config('coin.payments.ccapi.withdrawal_poll_stale_hours', 24));
        $anchor = $withdrawal->sent_at ?? $withdrawal->created_at;

        if ($anchor === null || $anchor->gt(now()->subHours($staleHours))) {
            return;
        }

        $cacheKey = 'withdrawal.poll.stale:'.$withdrawal->id;

        if (! Cache::add($cacheKey, true, now()->addHour())) {
            return;
        }

        $stats['stale']++;

        Log::warning('withdrawal.poll.stale_processing', [
            'withdrawal_id' => $withdrawal->id,
            'reference' => $withdrawal->reference,
            'gateway_request_id' => $withdrawal->gateway_request_id,
            'gateway_state' => $withdrawal->gateway_state,
            'sent_at' => $withdrawal->sent_at?->toIso8601String(),
            'stale_hours' => $staleHours,
        ]);
    }

    private function shouldAbandonAsMockGateway(Withdrawal $withdrawal): bool

    {

        return Withdrawal::isMockGatewayRequestId($withdrawal->gateway_request_id);

    }



    private function shouldAbandonAfterRepeatedPollErrors(Withdrawal $withdrawal): bool

    {

        $errorCount = PaymentWebhookLog::query()

            ->where('withdrawal_id', $withdrawal->id)

            ->where('event_type', 'payout_poll')

            ->where('processing_result', 'like', 'failed:%Poll error:%')

            ->count();



        $minErrors = (int) config('coin.payments.ccapi.withdrawal_poll_error_reject_count', 30);

        if ($errorCount >= $minErrors) {

            return true;

        }



        $staleHours = (int) config('coin.payments.ccapi.withdrawal_poll_stale_hours', 24);

        $anchor = $withdrawal->sent_at ?? $withdrawal->created_at;



        if ($anchor === null || $anchor->gt(now()->subHours($staleHours))) {

            return false;

        }



        return str_contains((string) $withdrawal->gateway_poll_summary, 'Poll error');

    }



    /** @param array{polled: int, completed: int, failed: int, pending: int, errors: int, abandoned: int} $stats */

    private function abandonWithdrawal(

        Withdrawal $withdrawal,

        string $statusReason,

        string $gatewayState,

        string $logMessage,

        array &$stats,

    ): void {

        $this->withdrawals->markFailedFromGateway(

            $withdrawal->fresh(),

            $gatewayState,

            statusReason: $statusReason,

            logSource: PaymentStatusLog::SOURCE_POLL,

        );



        $this->recordPollLog(

            $withdrawal,

            PaymentWebhookLog::RESULT_PROCESSED,

            $logMessage,

            [

                'gateway_request_id' => $withdrawal->gateway_request_id,

                'status_reason' => $statusReason,

            ],

        );



        $stats['abandoned']++;



        Log::warning('withdrawal.poll.abandoned', [

            'withdrawal_id' => $withdrawal->id,

            'reference' => $withdrawal->reference,

            'status_reason' => $statusReason,

        ]);

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

        try {

            PaymentWebhookLog::query()->create([

                'gateway' => (string) config('coin.payments.driver', 'mock'),

                'event_type' => 'payout_poll',

                'payload' => $payload,

                'signature_valid' => true,

                'idempotency_key' => 'poll:'.$withdrawal->id.':'.now()->format('Y-m-d-H-i-s.u'),

                'withdrawal_id' => $withdrawal->id,

                'processing_result' => PaymentWebhookLog::formatProcessingResult($result, $message),

                'processed_at' => now(),

            ]);

        } catch (UniqueConstraintViolationException) {

            // Concurrent poll workers may attempt the same audit row.

        }

    }

}



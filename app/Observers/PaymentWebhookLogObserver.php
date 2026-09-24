<?php

namespace App\Observers;

use App\Models\PaymentWebhookLog;
use App\Services\Payment\PaymentStatusLogService;
use App\Support\PaymentStatusDecoder;

class PaymentWebhookLogObserver
{
    public function __construct(
        private readonly PaymentStatusLogService $logs,
    ) {}

    public function saved(PaymentWebhookLog $log): void
    {
        if (! filled($log->processing_result)) {
            return;
        }

        [$result] = PaymentStatusDecoder::splitProcessingResult($log->processing_result);

        if ($result === PaymentWebhookLog::RESULT_DUPLICATE) {
            return;
        }

        if ($result === PaymentWebhookLog::RESULT_PROCESSED && ($log->deposit_id || $log->withdrawal_id)) {
            return;
        }

        if ($this->shouldSkipJournalImport($log, $result)) {
            return;
        }

        $this->logs->recordFromWebhookLog($log);
    }

    private function shouldSkipJournalImport(PaymentWebhookLog $log, ?string $result): bool
    {
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
}

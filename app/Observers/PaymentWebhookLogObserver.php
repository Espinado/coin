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

        $this->logs->recordFromWebhookLog($log);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\PaymentStatusLog;
use App\Services\Payment\PaymentLogGroupQuery;
use App\Support\PaymentStatusDecoder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentLogController extends Controller
{
    use AdminListQuery;

    public function __construct(
        private readonly PaymentLogGroupQuery $groupQuery,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.payment-logs.index', [
            'groups' => $this->groupQuery->paginate($request, $this->adminPerPage($request)),
            'entityType' => $request->string('entity_type')->toString(),
            'source' => $request->string('source')->toString(),
            'result' => $request->string('result')->toString(),
            'entityTypes' => $this->entityTypes(),
            'sources' => $this->sources(),
            'results' => $this->results(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(PaymentStatusLog $paymentLog): View
    {
        $paymentLog->load(['user', 'deposit.user', 'withdrawal.user']);

        return view('admin.payment-logs.show', [
            'log' => $paymentLog,
        ]);
    }

    /** @return array<string, string> */
    private function entityTypes(): array
    {
        return [
            'deposit' => PaymentStatusDecoder::entityTypeLabel('deposit'),
            'withdrawal' => PaymentStatusDecoder::entityTypeLabel('withdrawal'),
        ];
    }

    /** @return array<string, string> */
    private function sources(): array
    {
        return [
            PaymentStatusLog::SOURCE_IPN => PaymentStatusDecoder::sourceLabel(PaymentStatusLog::SOURCE_IPN),
            PaymentStatusLog::SOURCE_POLL => PaymentStatusDecoder::sourceLabel(PaymentStatusLog::SOURCE_POLL),
            PaymentStatusLog::SOURCE_APP => PaymentStatusDecoder::sourceLabel(PaymentStatusLog::SOURCE_APP),
            PaymentStatusLog::SOURCE_ADMIN => PaymentStatusDecoder::sourceLabel(PaymentStatusLog::SOURCE_ADMIN),
            PaymentStatusLog::SOURCE_SIMULATOR => PaymentStatusDecoder::sourceLabel(PaymentStatusLog::SOURCE_SIMULATOR),
        ];
    }

    /** @return array<string, string> */
    private function results(): array
    {
        return [
            'processed' => PaymentStatusDecoder::webhookResult('processed'),
            'ignored' => PaymentStatusDecoder::webhookResult('ignored'),
            'failed' => PaymentStatusDecoder::webhookResult('failed'),
            'duplicate' => PaymentStatusDecoder::webhookResult('duplicate'),
            'info' => PaymentStatusDecoder::webhookResult('info'),
        ];
    }
}

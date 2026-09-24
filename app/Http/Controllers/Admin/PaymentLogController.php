<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\PaymentStatusLog;
use App\Support\PaymentStatusDecoder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentLogController extends Controller
{
    use AdminListQuery;

    public function index(Request $request): View
    {
        $entityType = $request->string('entity_type')->toString();
        $source = $request->string('source')->toString();
        $result = $request->string('result')->toString();
        $search = $this->adminSearchTerm($request);

        $withdrawalId = $request->integer('withdrawal_id') ?: null;
        $depositId = $request->integer('deposit_id') ?: null;

        $query = PaymentStatusLog::query()
            ->with(['user', 'deposit', 'withdrawal'])
            ->when($entityType !== '', fn ($query) => $query->where('entity_type', $entityType))
            ->when($source !== '', fn ($query) => $query->where('source', $source))
            ->when($withdrawalId, fn ($query) => $query->where('withdrawal_id', $withdrawalId))
            ->when($depositId, fn ($query) => $query->where('deposit_id', $depositId))
            ->when($result !== '', fn ($query) => $query->where('result', $result))
            ->when($result === '', fn ($query) => $query->where(function ($inner) {
                $inner->whereNull('result')
                    ->orWhere('result', '!=', 'duplicate');
            }))
            ->where(function ($query) {
                $query->where('event_type', '!=', 'payout_poll')
                    ->orWhere('result', '!=', 'ignored');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $id = (int) $search;
                        $inner->orWhere('deposit_id', $id)
                            ->orWhere('withdrawal_id', $id);
                    }

                    $inner->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%"));
                });
            });

        $this->adminApplySort($request, $query, [
            'id' => 'id',
            'created_at' => 'created_at',
            'entity_type' => 'entity_type',
            'source' => 'source',
            'result' => 'result',
        ], 'created_at', 'desc', [
            'user' => fn ($logQuery, $direction) => $this->adminOrderByRelatedUser($logQuery, 'email', $direction),
        ]);

        return view('admin.payment-logs.index', [
            'logs' => $this->adminPaginate($query, $request),
            'entityType' => $entityType,
            'source' => $source,
            'result' => $result,
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

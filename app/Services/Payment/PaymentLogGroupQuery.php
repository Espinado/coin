<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\Withdrawal;
use App\Support\PaymentLogGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentLogGroupQuery
{
    public function paginate(Request $request, int $perPage): LengthAwarePaginator
    {
        $filtered = $this->filteredLogsQuery($request, applyListFilters: true);

        $groupKeySql = <<<'SQL'
CASE
    WHEN deposit_id IS NOT NULL THEN CONCAT('deposit:', deposit_id)
    WHEN withdrawal_id IS NOT NULL THEN CONCAT('withdrawal:', withdrawal_id)
    ELSE CONCAT('reference:', COALESCE(reference, ''))
END
SQL;

        $groupsSub = (clone $filtered)
            ->select([
                DB::raw("{$groupKeySql} as group_key"),
                'entity_type',
                'deposit_id',
                'withdrawal_id',
                DB::raw('MAX(reference) as reference'),
                DB::raw('MAX(created_at) as last_event_at'),
                DB::raw('MAX(id) as latest_log_id'),
            ])
            ->groupBy(DB::raw($groupKeySql), 'entity_type', 'deposit_id', 'withdrawal_id');

        $total = DB::query()->fromSub($groupsSub, 'payment_log_groups')->count();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $direction = strtolower($request->string('dir')->toString()) === 'asc' ? 'asc' : 'desc';
        $sort = $request->string('sort')->toString();

        $orderedGroups = DB::query()
            ->fromSub($groupsSub, 'payment_log_groups')
            ->when($sort === 'entity_type', fn ($query) => $query->orderBy('entity_type', $direction))
            ->when($sort === 'reference', fn ($query) => $query->orderBy('reference', $direction))
            ->when($sort === 'id', fn ($query) => $query->orderBy('latest_log_id', $direction))
            ->when(! in_array($sort, ['entity_type', 'reference', 'id'], true), fn ($query) => $query->orderBy('last_event_at', $direction))
            ->orderByDesc('latest_log_id')
            ->offset(max(0, ($page - 1) * $perPage))
            ->limit($perPage)
            ->get();

        if ($orderedGroups->isEmpty()) {
            return new LengthAwarePaginator([], $total, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $eventsByGroup = $this->loadEventsForGroups($request, $orderedGroups);

        $items = $orderedGroups
            ->map(function ($row) use ($eventsByGroup) {
                $events = $eventsByGroup->get($row->group_key, collect());

                return new PaymentLogGroup(
                    entityType: (string) $row->entity_type,
                    depositId: $row->deposit_id !== null ? (int) $row->deposit_id : null,
                    withdrawalId: $row->withdrawal_id !== null ? (int) $row->withdrawal_id : null,
                    reference: $row->reference !== null ? (string) $row->reference : null,
                    events: $events,
                );
            })
            ->values();

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    public function filteredLogsQuery(Request $request, bool $applyListFilters = true): Builder
    {
        $entityType = $request->string('entity_type')->toString();
        $source = $request->string('source')->toString();
        $result = $request->string('result')->toString();
        $search = trim($request->string('q')->toString());

        $withdrawalId = $request->integer('withdrawal_id') ?: null;
        $depositId = $request->integer('deposit_id') ?: null;

        $query = PaymentStatusLog::query()
            ->where(function ($query) {
                $query->whereNotNull('deposit_id')
                    ->orWhereNotNull('withdrawal_id')
                    ->orWhereNotNull('reference');
            })
            ->when($entityType !== '', fn ($query) => $query->where('entity_type', $entityType))
            ->when($applyListFilters && $source !== '', fn ($query) => $query->where('source', $source))
            ->when($withdrawalId, fn ($query) => $query->where('withdrawal_id', $withdrawalId))
            ->when($depositId, fn ($query) => $query->where('deposit_id', $depositId))
            ->when($applyListFilters && $result !== '', fn ($query) => $query->where('result', $result))
            ->when($applyListFilters && $result === '', fn ($query) => $query->where(function ($inner) {
                $inner->whereNull('result')
                    ->orWhere('result', '!=', 'duplicate');
            }))
            ->where(function ($query) {
                $query->where('event_type', '!=', 'payout_poll')
                    ->orWhere(function ($poll) {
                        $poll->where('result', '!=', 'ignored')
                            ->where(function ($failed) {
                                $failed->where('result', '!=', 'failed')
                                    ->orWhere(function ($onlyOpen) {
                                        $onlyOpen->where(function ($withdrawalOpen) {
                                            $withdrawalOpen->whereNull('withdrawal_id')
                                                ->orWhereExists(function ($exists) {
                                                    $exists->selectRaw('1')
                                                        ->from('withdrawals')
                                                        ->whereColumn('withdrawals.id', 'payment_status_logs.withdrawal_id')
                                                        ->where('withdrawals.status', Withdrawal::STATUS_PROCESSING);
                                                });
                                        })->where(function ($depositOpen) {
                                            $depositOpen->whereNull('deposit_id')
                                                ->orWhereExists(function ($exists) {
                                                    $exists->selectRaw('1')
                                                        ->from('deposits')
                                                        ->whereColumn('deposits.id', 'payment_status_logs.deposit_id')
                                                        ->where('deposits.status', Deposit::STATUS_PENDING);
                                                });
                                        });
                                    });
                            });
                    });
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

        return $query;
    }

    /** @param  Collection<int, object>  $groupRows */
    private function loadEventsForGroups(Request $request, Collection $groupRows): Collection
    {
        $depositIds = $groupRows->pluck('deposit_id')->filter()->map(fn ($id) => (int) $id)->values();
        $withdrawalIds = $groupRows->pluck('withdrawal_id')->filter()->map(fn ($id) => (int) $id)->values();
        $references = $groupRows
            ->filter(fn ($row) => $row->deposit_id === null && $row->withdrawal_id === null)
            ->pluck('reference')
            ->filter()
            ->values();

        $eventsQuery = $this->filteredLogsQuery($request, applyListFilters: false)
            ->with(['user', 'deposit.user', 'withdrawal.user'])
            ->where(function ($query) use ($depositIds, $withdrawalIds, $references) {
                $hasConstraint = false;

                if ($depositIds->isNotEmpty()) {
                    $query->whereIn('deposit_id', $depositIds);
                    $hasConstraint = true;
                }

                if ($withdrawalIds->isNotEmpty()) {
                    $hasConstraint
                        ? $query->orWhereIn('withdrawal_id', $withdrawalIds)
                        : $query->whereIn('withdrawal_id', $withdrawalIds);
                    $hasConstraint = true;
                }

                if ($references->isNotEmpty()) {
                    $referenceScope = function ($inner) use ($references) {
                        $inner->whereNull('deposit_id')
                            ->whereNull('withdrawal_id')
                            ->whereIn('reference', $references);
                    };

                    $hasConstraint
                        ? $query->orWhere($referenceScope)
                        : $query->where($referenceScope);
                }
            })
            ->orderBy('created_at')
            ->orderBy('id');

        return $eventsQuery
            ->get()
            ->groupBy(fn (PaymentStatusLog $log) => PaymentLogGroup::groupKey($log));
    }
}

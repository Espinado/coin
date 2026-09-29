<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminDateRangeFilter;
use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\EarlyUnlockRequest;
use App\Models\PlatformCommission;
use App\Models\Withdrawal;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CommissionController extends Controller
{
    use AdminDateRangeFilter;
    use AdminListQuery;

    public function index(Request $request, PlatformSettingsService $settings): View
    {
        $search = $this->adminSearchTerm($request);
        [$rangeStart, $rangeEnd, $period, $from, $to] = $this->adminDateRangeState($request);

        $rows = $this->commissionRows($search, $rangeStart, $rangeEnd);
        $totalCommission = round((float) $rows->sum('amount'), 2);

        $sort = $request->string('sort')->toString() ?: 'processed_at';
        $dir = strtolower($request->string('dir')->toString() ?: 'desc') === 'asc' ? 'asc' : 'desc';

        $sorted = $rows->sortBy(function (array $row) use ($sort) {
            return match ($sort) {
                'reference' => $row['reference'],
                'user' => $row['user_email'] ?? '',
                'amount', 'platform_fee' => $row['amount'],
                'kind' => $row['kind'],
                default => $row['processed_at_ts'],
            };
        }, SORT_REGULAR, $dir === 'desc')->values();

        $perPage = $this->adminPerPage($request);
        $page = max(1, (int) $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('admin.commissions.index', [
            'commissions' => $paginator,
            'totalCommission' => $totalCommission,
            'currency' => $settings->tokenSymbol(),
            'period' => $period,
            'from' => $from,
            'to' => $to,
            ...$this->adminListState($request),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function commissionRows(string $search, mixed $rangeStart, mixed $rangeEnd): Collection
    {
        $withdrawalQuery = Withdrawal::query()
            ->with('user')
            ->where('status', Withdrawal::STATUS_PAID)
            ->where('platform_fee', '>', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $inner->orWhereKey((int) $search);
                    }

                    $inner->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('account_slug', 'like', "%{$search}%"));
                });
            });

        $this->adminApplyDateRange($withdrawalQuery, $rangeStart, $rangeEnd, 'processed_at');

        $withdrawalRows = $withdrawalQuery->get()->map(function (Withdrawal $withdrawal) {
            $processedAt = $withdrawal->processed_at;

            return [
                'kind' => PlatformCommission::KIND_WITHDRAWAL,
                'kind_label' => __('coin.admin.commission_kind.withdrawal'),
                'reference' => $withdrawal->reference,
                'user_email' => $withdrawal->user?->email,
                'base_amount_label' => $withdrawal->formattedAmount(),
                'amount' => round((float) $withdrawal->platform_fee, 2),
                'processed_at' => $processedAt,
                'processed_at_ts' => $processedAt?->timestamp ?? 0,
                'url' => route('admin.withdrawals.show', $withdrawal),
            ];
        });

        $earlyQuery = EarlyUnlockRequest::query()
            ->with('user')
            ->where('status', EarlyUnlockRequest::STATUS_APPROVED)
            ->where('fee_amount', '>', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $inner->orWhereKey((int) $search);
                    }

                    $inner->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('account_slug', 'like', "%{$search}%"));
                });
            });

        $this->adminApplyDateRange($earlyQuery, $rangeStart, $rangeEnd, 'processed_at');

        $earlyRows = $earlyQuery->get()->map(function (EarlyUnlockRequest $request) {
            $processedAt = $request->processed_at;

            return [
                'kind' => PlatformCommission::KIND_EARLY_UNLOCK,
                'kind_label' => __('coin.admin.commission_kind.early_unlock'),
                'reference' => $request->reference,
                'user_email' => $request->user?->email,
                'base_amount_label' => $request->formattedPrincipal(),
                'amount' => round((float) $request->fee_amount, 2),
                'processed_at' => $processedAt,
                'processed_at_ts' => $processedAt?->timestamp ?? 0,
                'url' => route('admin.early-unlocks.show', $request),
            ];
        });

        return $withdrawalRows->concat($earlyRows)->values();
    }
}

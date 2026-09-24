<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminDateRangeFilter;
use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    use AdminDateRangeFilter;
    use AdminListQuery;

    public function index(Request $request, PlatformSettingsService $settings): View
    {
        $search = $this->adminSearchTerm($request);
        [$rangeStart, $rangeEnd, $period, $from, $to] = $this->adminDateRangeState($request);

        $query = Withdrawal::query()
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

        $this->adminApplyDateRange($query, $rangeStart, $rangeEnd);

        $totalCommission = (float) (clone $query)->sum('platform_fee');

        $this->adminApplySort($request, $query, [
            'id' => 'id',
            'processed_at' => 'processed_at',
            'platform_fee' => 'platform_fee',
            'amount' => 'amount',
            'reference' => 'reference',
        ], 'processed_at', 'desc', [
            'user' => fn ($withdrawalQuery, $direction) => $this->adminOrderByRelatedUser($withdrawalQuery, 'email', $direction),
        ]);

        return view('admin.commissions.index', [
            'commissions' => $this->adminPaginate($query, $request),
            'totalCommission' => $totalCommission,
            'currency' => $settings->tokenSymbol(),
            'period' => $period,
            'from' => $from,
            'to' => $to,
            ...$this->adminListState($request),
        ]);
    }
}

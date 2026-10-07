<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminDateRangeFilter;
use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\SiteVisitUnique;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitStatsController extends Controller
{
    use AdminDateRangeFilter;
    use AdminListQuery;

    public function index(Request $request): View
    {
        $requestedPeriod = $request->string('period')->toString();
        if ($requestedPeriod === '') {
            $request->merge(['period' => 'month']);
            $requestedPeriod = 'month';
        }

        if ($requestedPeriod === 'all') {
            $rangeStart = null;
            $rangeEnd = null;
            $period = 'all';
            $from = '';
            $to = '';
        } else {
            [$rangeStart, $rangeEnd, $period, $from, $to] = $this->adminDateRangeState($request);
        }

        $query = SiteVisitUnique::query()
            ->select([
                'visit_date',
                DB::raw('COUNT(*) as unique_ips'),
                DB::raw('SUM(hits) as hits'),
            ])
            ->groupBy('visit_date');

        $this->applyVisitDateRange($query, $rangeStart, $rangeEnd);

        $sort = $request->string('sort')->toString() ?: 'visit_date';
        $dir = strtolower($request->string('dir')->toString() ?: 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['visit_date', 'unique_ips', 'hits'], true)) {
            $sort = 'visit_date';
        }

        $query->orderBy($sort, $dir);

        $days = $this->adminPaginate($query, $request);

        $totalsQuery = SiteVisitUnique::query();
        $this->applyVisitDateRange($totalsQuery, $rangeStart, $rangeEnd);

        $uniqueIps = (int) (clone $totalsQuery)->selectRaw('count(distinct ip_hash) as aggregate')->value('aggregate');
        $hits = (int) (clone $totalsQuery)->sum('hits');
        $activeDays = (int) (clone $totalsQuery)->selectRaw('count(distinct visit_date) as aggregate')->value('aggregate');

        return view('admin.visits.index', [
            'days' => $days,
            'uniqueIps' => $uniqueIps,
            'hits' => $hits,
            'activeDays' => $activeDays,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'sort' => $sort,
            'dir' => $dir,
            ...$this->adminListState($request),
        ]);
    }

    private function applyVisitDateRange(Builder $query, ?Carbon $start, ?Carbon $end): void
    {
        if ($start !== null) {
            $query->whereDate('visit_date', '>=', $start->toDateString());
        }

        if ($end !== null) {
            $query->whereDate('visit_date', '<=', $end->toDateString());
        }
    }
}

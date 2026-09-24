<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\LocaleFormat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AdminDateRangeFilter
{
    /** @return array{0: ?Carbon, 1: ?Carbon, period: string, from: string, to: string} */
    protected function adminDateRangeState(Request $request, string $column = 'processed_at'): array
    {
        $period = $request->string('period')->toString();
        $from = trim($request->string('from')->toString());
        $to = trim($request->string('to')->toString());
        $timezone = LocaleFormat::displayTimezone();
        $now = now($timezone);

        if ($period === 'today') {
            return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), $period, '', ''];
        }

        if ($period === 'week') {
            return [$now->copy()->startOfWeek(), $now->copy()->endOfDay(), $period, '', ''];
        }

        if ($period === 'month') {
            return [$now->copy()->startOfMonth(), $now->copy()->endOfDay(), $period, '', ''];
        }

        if ($period === 'custom') {
            $start = $from !== '' ? Carbon::parse($from, $timezone)->startOfDay() : null;
            $end = $to !== '' ? Carbon::parse($to, $timezone)->endOfDay() : null;

            return [$start, $end, $period, $from, $to];
        }

        return [null, null, '', $from, $to];
    }

    protected function adminApplyDateRange(Builder $query, ?Carbon $start, ?Carbon $end, string $column = 'processed_at'): void
    {
        if ($start !== null) {
            $query->where($column, '>=', $start->copy()->utc());
        }

        if ($end !== null) {
            $query->where($column, '<=', $end->copy()->utc());
        }
    }
}

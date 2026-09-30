<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminDateRangeFilter;
use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    use AdminDateRangeFilter;
    use AdminListQuery;

    public function index(Request $request): View
    {
        $search = $this->adminSearchTerm($request);
        [$rangeStart, $rangeEnd, $period, $from, $to] = $this->adminDateRangeState($request);
        $period = $period !== '' ? $period : 'all';
        $level = $request->string('level')->toString();
        $source = $request->string('source')->toString();

        $query = SystemLog::query();

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('message', 'like', "%{$search}%")
                    ->orWhere('channel', 'like', "%{$search}%")
                    ->orWhere('exception_class', 'like', "%{$search}%")
                    ->orWhere('file', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $inner->orWhereKey((int) $search);
                }
            });
        }

        if ($level !== '') {
            $query->where('level', $level);
        }

        if ($source !== '') {
            $query->where('source', $source);
        }

        $this->adminApplyDateRange($query, $rangeStart, $rangeEnd, 'occurred_at');

        $this->adminApplySort($request, $query, [
            'occurred_at' => 'occurred_at',
            'level' => 'level',
            'source' => 'source',
            'channel' => 'channel',
            'id' => 'id',
        ], 'occurred_at', 'desc');

        return view('admin.system-logs.index', [
            'logs' => $this->adminPaginate($query, $request),
            'level' => $level,
            'source' => $source,
            'levels' => $this->levels(),
            'sources' => $this->sources(),
            'period' => $period,
            'from' => $from,
            'to' => $to,
            ...$this->adminListState($request),
        ]);
    }

    public function show(SystemLog $systemLog): View
    {
        return view('admin.system-logs.show', [
            'log' => $systemLog,
        ]);
    }

    /** @return array<string, string> */
    private function levels(): array
    {
        return [
            SystemLog::LEVEL_CRITICAL => __('coin.admin.system_log_level.critical'),
            SystemLog::LEVEL_ERROR => __('coin.admin.system_log_level.error'),
            SystemLog::LEVEL_WARNING => __('coin.admin.system_log_level.warning'),
            SystemLog::LEVEL_INFO => __('coin.admin.system_log_level.info'),
        ];
    }

    /** @return array<string, string> */
    private function sources(): array
    {
        return [
            SystemLog::SOURCE_CRON => __('coin.admin.system_log_source.cron'),
            SystemLog::SOURCE_SCHEDULE => __('coin.admin.system_log_source.schedule'),
            SystemLog::SOURCE_MAIL => __('coin.admin.system_log_source.mail'),
            SystemLog::SOURCE_QUEUE => __('coin.admin.system_log_source.queue'),
            SystemLog::SOURCE_HTTP => __('coin.admin.system_log_source.http'),
            SystemLog::SOURCE_APP => __('coin.admin.system_log_source.app'),
        ];
    }
}

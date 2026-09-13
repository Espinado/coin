<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReverbConnectionStatsCommand extends Command
{
    protected $signature = 'coin:reverb-connection-stats
                            {--date= : Date to summarize (Y-m-d), default today}
                            {--days=1 : Number of days ending on --date}';

    protected $description = 'Summarize WebSocket disconnects and Reverb watchdog events from logs';

    /** @var array<string, int> */
    private array $clientCounts = [
        'connected' => 0,
        'disconnected' => 0,
        'unavailable' => 0,
        'failed' => 0,
        'error' => 0,
        'connecting' => 0,
    ];

    /** @var array<string, int> */
    private array $watchdogCounts = [
        'ALREADY_OK' => 0,
        'PROCESS_OK' => 0,
        'PROCESS_FAIL' => 0,
        'RESTART' => 0,
    ];

    public function handle(): int
    {
        $endDate = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now();

        $days = max(1, (int) $this->option('days'));
        $startDate = $endDate->copy()->subDays($days - 1);

        $this->components->info(sprintf(
            'Reverb connection stats (%s — %s)',
            $startDate->toDateString(),
            $endDate->toDateString(),
        ));

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $this->parseClientLog($date->toDateString());
        }

        $this->parseWatchdogLog($startDate, $endDate);

        $this->newLine();
        $this->line('Browser WebSocket events (from reverb-connection.log):');

        foreach ($this->clientCounts as $event => $count) {
            if ($count === 0) {
                continue;
            }

            $this->line(sprintf('  %s: %d', $event, $count));
        }

        if (array_sum($this->clientCounts) === 0) {
            $this->line('  (no client events in range)');
        }

        $this->newLine();
        $this->line('Reverb watchdog (from reverb-watchdog.log):');

        foreach ($this->watchdogCounts as $event => $count) {
            if ($count === 0) {
                continue;
            }

            $this->line(sprintf('  %s: %d', $event, $count));
        }

        if (array_sum($this->watchdogCounts) === 0) {
            $this->line('  (no watchdog events in range)');
        }

        $drops = $this->clientCounts['disconnected']
            + $this->clientCounts['unavailable']
            + $this->clientCounts['failed'];

        $this->newLine();
        $this->components->info(sprintf(
            'Total client-side drop events (disconnected + unavailable + failed): %d',
            $drops,
        ));

        return self::SUCCESS;
    }

    private function parseClientLog(string $date): void
    {
        $paths = [
            storage_path('logs/reverb-connection-'.$date.'.log'),
            storage_path('logs/reverb-connection.log'),
        ];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            foreach (File::lines($path) as $line) {
                if ($date !== now()->toDateString() && ! str_contains($line, $date)) {
                    continue;
                }

                if (! str_contains($line, '[ws_state]')) {
                    continue;
                }

                foreach (array_keys($this->clientCounts) as $event) {
                    if (str_contains($line, '[ws_state] '.$event) || str_contains($line, '[ws_state] initial:'.$event)) {
                        $this->clientCounts[$event]++;
                        break;
                    }
                }
            }
        }
    }

    private function parseWatchdogLog(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): void
    {
        $path = storage_path('logs/reverb-watchdog.log');

        if (! File::exists($path)) {
            return;
        }

        foreach (File::lines($path) as $line) {
            $lineDate = $this->extractLineDate($line);

            if ($lineDate !== null) {
                $parsed = \Carbon\Carbon::parse($lineDate)->startOfDay();

                if ($parsed->lt($startDate->copy()->startOfDay()) || $parsed->gt($endDate->copy()->startOfDay())) {
                    continue;
                }
            }

            if (str_contains($line, 'ALREADY_OK')) {
                $this->watchdogCounts['ALREADY_OK']++;
            }

            if (str_contains($line, 'PROCESS_OK')) {
                $this->watchdogCounts['PROCESS_OK']++;
            }

            if (str_contains($line, 'PROCESS_FAIL')) {
                $this->watchdogCounts['PROCESS_FAIL']++;
            }

            if (str_contains($line, 'Started PID') || str_contains($line, '[watchdog] RESTART')) {
                $this->watchdogCounts['RESTART']++;
            }
        }
    }

    private function extractLineDate(string $line): ?string
    {
        if (preg_match('/\[(\d{4}-\d{2}-\d{2})T/', $line, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\[(\d{4}-\d{2}-\d{2}) /', $line, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

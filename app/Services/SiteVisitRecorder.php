<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteVisitRecorder
{
    private static ?bool $tableReady = null;

    public function shouldRecord(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->header('X-Livewire') !== null) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');

        if ($this->isExcludedPath($path)) {
            return false;
        }

        if ($this->isBot((string) $request->userAgent())) {
            return false;
        }

        $ip = trim((string) $request->ip());

        return $ip !== '' && $ip !== '0.0.0.0';
    }

    public function record(Request $request): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $ip = trim((string) $request->ip());
        if ($ip === '') {
            return;
        }

        $visitDate = now()->toDateString();
        $ipHash = hash_hmac('sha256', $ip, (string) config('app.key'));
        $cacheKey = "site_visit:{$visitDate}:{$ipHash}";

        try {
            if (Cache::add($cacheKey, 1, now()->endOfDay())) {
                $now = now();
                DB::table('site_visit_uniques')->insertOrIgnore([
                    'visit_date' => $visitDate,
                    'ip_hash' => $ipHash,
                    'hits' => 1,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                return;
            }

            // Throttle hit increments to keep write load low on busy pages.
            if (! Cache::add("{$cacheKey}:hit", 1, now()->addMinutes(5))) {
                return;
            }

            DB::table('site_visit_uniques')
                ->where('visit_date', $visitDate)
                ->where('ip_hash', $ipHash)
                ->update([
                    'hits' => DB::raw('hits + 1'),
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (Throwable) {
            // Visit stats must never break the request.
        }
    }

    private function isExcludedPath(string $path): bool
    {
        if ($path === '/up' || str_starts_with($path, '/livewire')) {
            return true;
        }

        $excluded = [
            '/robots.txt',
            '/sitemap.xml',
            '/llms.txt',
            '/llms-full.txt',
            '/webhooks/ccapi',
            '/reverb-debug',
            '/guest/broadcasting/auth',
        ];

        if (in_array($path, $excluded, true)) {
            return true;
        }

        // IndexNow key files: /{key}.txt
        if (preg_match('#^/[A-Za-z0-9-]{8,128}\\.txt$#', $path) === 1) {
            return true;
        }

        return false;
    }

    private function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|facebookexternalhit|preview|wget|curl|python-requests|httpclient|scrapy|pingdom|uptimerobot|headless/i',
            $userAgent,
        );
    }

    private function tableReady(): bool
    {
        if (self::$tableReady !== null) {
            return self::$tableReady;
        }

        try {
            self::$tableReady = Schema::hasTable('site_visit_uniques');
        } catch (Throwable) {
            self::$tableReady = false;
        }

        return self::$tableReady;
    }
}

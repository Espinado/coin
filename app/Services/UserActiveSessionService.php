<?php

namespace App\Services;

use App\Models\User;
use App\Support\LocaleFormat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserActiveSessionService
{
    /** @return Collection<int, object{id: string, ip_address: ?string, user_agent: ?string, last_activity: int}> */
    public function activeForUser(int $userId): Collection
    {
        $cutoff = now()->subMinutes((int) config('session.lifetime', 120))->getTimestamp();

        return DB::table('sessions')
            ->where('user_id', $userId)
            ->where('last_activity', '>=', $cutoff)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);
    }

    public function countForUser(int $userId): int
    {
        return $this->activeForUser($userId)->count();
    }

    public function summaryForUser(User $user): string
    {
        $sessions = $this->activeForUser((int) $user->id);
        $count = $sessions->count();

        if ($count === 0) {
            return __('coin.profile.sessions_summary_empty');
        }

        $latest = $sessions->first();
        $date = LocaleFormat::shortMonthDay(
            Carbon::createFromTimestamp((int) $latest->last_activity),
        );

        return trans_choice('coin.profile.sessions_summary', $count, [
            'count' => $count,
            'date' => $date,
        ]);
    }

    /** @return list<array{id: string, label: string, ip: ?string, last_active: string, is_current: bool}> */
    public function listForUser(User $user, string $currentSessionId): array
    {
        return $this->activeForUser((int) $user->id)
            ->map(function (object $session) use ($currentSessionId): array {
                return [
                    'id' => (string) $session->id,
                    'label' => $this->describeUserAgent($session->user_agent),
                    'ip' => $session->ip_address ?: null,
                    'last_active' => LocaleFormat::shortDateTime(
                        Carbon::createFromTimestamp((int) $session->last_activity),
                    ),
                    'is_current' => (string) $session->id === $currentSessionId,
                ];
            })
            ->values()
            ->all();
    }

    public function revokeForUser(User $user, string $sessionId): bool
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->delete() > 0;
    }

    public function revokeOthersForUser(User $user, string $currentSessionId): int
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    public function describeUserAgent(?string $userAgent): string
    {
        if ($userAgent === null || trim($userAgent) === '') {
            return __('coin.profile.sessions_unknown_device');
        }

        $ua = strtolower($userAgent);
        $browser = __('coin.profile.sessions_browser_unknown');

        if (str_contains($ua, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'chrome/') && ! str_contains($ua, 'edg/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
            $browser = 'Opera';
        }

        $device = __('coin.profile.sessions_device_desktop');

        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
            $device = str_contains($ua, 'ipad')
                ? __('coin.profile.sessions_device_tablet')
                : __('coin.profile.sessions_device_phone');
        } elseif (str_contains($ua, 'android')) {
            $device = str_contains($ua, 'mobile')
                ? __('coin.profile.sessions_device_phone')
                : __('coin.profile.sessions_device_tablet');
        } elseif (str_contains($ua, 'mobile')) {
            $device = __('coin.profile.sessions_device_phone');
        }

        return $browser.' · '.$device;
    }
}

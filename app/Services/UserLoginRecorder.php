<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserLoginRecorder
{
    public const SESSION_KEY = 'coin_login_recorded';

    public function record(User $user, Request $request): void
    {
        $user->forceFill(['last_login_at' => now()])->save();
        $request->session()->put(self::SESSION_KEY, true);
    }

    public function shouldRecord(Request $request): bool
    {
        $user = $request->user('web');

        return $user instanceof User
            && $user->hasVerifiedEmail()
            && ! $request->session()->get(self::SESSION_KEY);
    }

    public function backfillMissing(): int
    {
        $updated = 0;

        User::query()
            ->whereNull('last_login_at')
            ->orderBy('id')
            ->each(function (User $user) use (&$updated): void {
                $timestamp = $this->guessLastActivityAt($user);

                if ($timestamp === null) {
                    return;
                }

                $user->forceFill(['last_login_at' => $timestamp])->save();
                $updated++;
            });

        return $updated;
    }

    private function guessLastActivityAt(User $user): ?Carbon
    {
        $candidates = array_filter([
            $user->email_verified_at,
            $user->contracts()->max('created_at'),
            $user->walletTransactions()->max('created_at'),
            $user->created_at,
        ]);

        if ($candidates === []) {
            return null;
        }

        return collect($candidates)
            ->map(fn ($value) => $value instanceof Carbon ? $value : Carbon::parse($value))
            ->max();
    }
}

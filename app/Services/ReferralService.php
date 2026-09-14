<?php

namespace App\Services;

use App\Models\ReferralProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class ReferralService
{
    private const COOKIE_NAME = 'coin_referral_code';

    private const SESSION_KEY = 'referral_code';

    private const COOKIE_MINUTES = 60 * 24 * 30;

    public function storeCode(string $code): void
    {
        $code = $this->normalizeCode($code);

        session([self::SESSION_KEY => $code]);

        Cookie::queue(
            cookie(
                self::COOKIE_NAME,
                $code,
                self::COOKIE_MINUTES,
                '/',
                null,
                null,
                true,
                false,
                'lax',
            )
        );
    }

    public function storedCode(Request $request): ?string
    {
        $code = $request->cookie(self::COOKIE_NAME) ?? session(self::SESSION_KEY);

        if (! is_string($code) || $code === '') {
            return null;
        }

        return $this->normalizeCode($code);
    }

    public function findProfileByCode(?string $code): ?ReferralProfile
    {
        if (! filled($code)) {
            return null;
        }

        return ReferralProfile::query()
            ->where('code', $this->normalizeCode($code))
            ->first();
    }

    public function attributeReferrerOnSignup(User $user, Request $request): void
    {
        $code = $this->storedCode($request);
        $referrerProfile = $this->findProfileByCode($code);

        if ($referrerProfile && $referrerProfile->user_id !== $user->id) {
            $user->forceFill(['referred_by_user_id' => $referrerProfile->user_id])->save();

            $referrerProfile->increment('invited_count');
            $referrerProfile->increment('level1_users');
        }

        $this->ensureReferralProfile($user);
        $this->clearStoredCode();
    }

    public function ensureReferralProfile(User $user): ReferralProfile
    {
        $settings = app(PlatformSettingsService::class);

        return ReferralProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $this->generateUniqueCode(),
                'level1_percent' => $settings->getInt('referral_level1_percent'),
                'level2_percent' => $settings->getInt('referral_level2_percent'),
            ],
        );
    }

    public function generateUniqueCode(): string
    {
        do {
            $code = 'COIN-'.strtoupper(Str::random(5));
        } while (ReferralProfile::query()->where('code', $code)->exists());

        return $code;
    }

    public function clearStoredCode(): void
    {
        session()->forget(self::SESSION_KEY);
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}

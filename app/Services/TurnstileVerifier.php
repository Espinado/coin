<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    public function enabled(): bool
    {
        return (bool) config('coin.turnstile.enabled', false);
    }

    public function siteKey(): string
    {
        return (string) config('coin.turnstile.site_key', '');
    }

    /**
     * Verify a Turnstile response token. When disabled, always passes.
     * When enabled, missing secret/token or HTTP/API failure fails closed.
     */
    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        $secret = (string) config('coin.turnstile.secret_key', '');
        $token = trim((string) $token);

        if ($secret === '' || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('coin.turnstile.timeout_seconds', 5))
                ->post((string) config('coin.turnstile.verify_url'), array_filter([
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ], fn ($value) => $value !== null && $value !== ''));
        } catch (\Throwable $e) {
            Log::warning('turnstile.verify_failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('turnstile.verify_http_error', [
                'status' => $response->status(),
            ]);

            return false;
        }

        $payload = $response->json();

        return (bool) ($payload['success'] ?? false);
    }
}

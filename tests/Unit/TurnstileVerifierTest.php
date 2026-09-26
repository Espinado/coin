<?php

namespace Tests\Unit;

use App\Services\TurnstileVerifier;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TurnstileVerifierTest extends TestCase
{
    #[Test]
    public function it_passes_when_turnstile_is_disabled(): void
    {
        config([
            'coin.turnstile.enabled' => false,
            'coin.turnstile.secret_key' => '',
        ]);

        $this->assertTrue(app(TurnstileVerifier::class)->verify(null));
        $this->assertTrue(app(TurnstileVerifier::class)->verify(''));
    }

    #[Test]
    public function it_fails_closed_when_enabled_without_token_or_secret(): void
    {
        config([
            'coin.turnstile.enabled' => true,
            'coin.turnstile.secret_key' => '',
            'coin.turnstile.site_key' => 'site',
        ]);

        $this->assertFalse(app(TurnstileVerifier::class)->verify('token-abc'));

        config(['coin.turnstile.secret_key' => 'secret']);

        $this->assertFalse(app(TurnstileVerifier::class)->verify(''));
        $this->assertFalse(app(TurnstileVerifier::class)->verify(null));
    }

    #[Test]
    public function it_accepts_successful_siteverify_response(): void
    {
        config([
            'coin.turnstile.enabled' => true,
            'coin.turnstile.secret_key' => 'test-secret',
            'coin.turnstile.verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->assertTrue(app(TurnstileVerifier::class)->verify('valid-token', '127.0.0.1'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && $request['secret'] === 'test-secret'
                && $request['response'] === 'valid-token'
                && $request['remoteip'] === '127.0.0.1';
        });
    }

    #[Test]
    public function it_rejects_failed_or_unreachable_siteverify(): void
    {
        config([
            'coin.turnstile.enabled' => true,
            'coin.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']], 200),
        ]);

        $this->assertFalse(app(TurnstileVerifier::class)->verify('bad-token'));

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response('upstream error', 503),
        ]);

        $this->assertFalse(app(TurnstileVerifier::class)->verify('any-token'));
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Services\EmailVerificationCodeService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee(__('coin.auth.verify_email_title'), false);
        $response->assertSee(__('coin.auth.verify_email_unverified_notice'), false);
        $response->assertSee(__('coin.auth.verify_email_code'), false);
        Mail::assertSent(EmailVerificationMail::class);
    }

    public function test_email_can_be_verified_with_code(): void
    {
        Mail::fake();
        Event::fake([Verified::class]);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verify-email');

        Mail::assertSent(EmailVerificationMail::class, function (EmailVerificationMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $code = Mail::sent(EmailVerificationMail::class)->first()->code;

        $response = $this->actingAs($user)->post('/verify-email/code', [
            'code' => $code,
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNotNull($user->fresh()->last_login_at);
        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas('status', __('coin.auth.verify_email_confirmed_redirect'));
    }

    public function test_email_is_not_verified_with_invalid_code(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verify-email');

        $response = $this->actingAs($user)->from('/verify-email')->post('/verify-email/code', [
            'code' => '000000',
        ]);

        $response->assertRedirect('/verify-email');
        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_can_be_verified_via_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas('status', __('coin.auth.verify_email_confirmed_redirect'));
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_sends_new_verification_code(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        app(EmailVerificationCodeService::class)->sendCode($user);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        Mail::assertSent(EmailVerificationMail::class, 2);
        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-code-sent');
    }
}

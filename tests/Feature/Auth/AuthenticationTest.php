<?php

namespace Tests\Feature\Auth;

use App\Mail\LoginVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_without_two_factor_when_disabled(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        Mail::assertNothingSent();
    }

    public function test_users_with_two_factor_enabled_must_verify_email_code(): void
    {
        Mail::fake();

        $user = User::factory()->withEmailTwoFactor()->create();
        $code = null;

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login.two-factor', absolute: false));
        $this->assertGuest();

        Mail::assertSent(LoginVerificationMail::class, function (LoginVerificationMail $mail) use (&$code, $user) {
            $code = $mail->code;

            return $mail->hasTo($user->email);
        });

        $response = $this->post('/login/two-factor', [
            'code' => $code,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
        $response->assertSee(__('coin.auth.login_password_invalid'), false);
    }

    public function test_users_can_not_authenticate_with_unknown_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $response->assertSee(__('coin.auth.login_email_not_found'), false);
    }

    public function test_two_factor_challenge_expires_and_restarts_from_login(): void
    {
        Mail::fake();

        $user = User::factory()->withEmailTwoFactor()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor', absolute: false));

        $sessionId = session()->getId();
        \Illuminate\Support\Facades\Cache::forget('login_2fa:'.$sessionId);

        $this->get(route('login.two-factor', absolute: false))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHas('status', __('coin.auth.two_factor_expired'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor', absolute: false));

        Mail::assertSent(LoginVerificationMail::class, 2);
    }

    public function test_users_can_authenticate_with_mixed_case_email(): void
    {
        $user = User::factory()->create([
            'email' => 'mixedcase@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'MixedCase@Example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_unverified_user_login_redirects_to_verification_with_code_sent(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.notice', absolute: false));
        $response->assertSessionHas('status', __('coin.auth.email_not_verified_login_sent'));
        Mail::assertSent(\App\Mail\EmailVerificationMail::class);
    }

    public function test_unverified_user_can_resume_email_verification(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->post('/verify-email/resume', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.notice', absolute: false));
        Mail::assertSent(\App\Mail\EmailVerificationMail::class);
    }
}

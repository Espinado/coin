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

    public function test_verified_users_must_always_verify_email_code_on_login(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_two_factor_enabled' => false,
        ]);
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
    }

    public function test_users_can_not_authenticate_with_unknown_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_cancel_clears_two_factor_challenge(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor', absolute: false));

        $this->get('/login?cancel=1')
            ->assertOk()
            ->assertSee(__('coin.auth.two_factor_cancelled'), false);

        $this->get('/login/two-factor')
            ->assertRedirect(route('login', absolute: false));
    }

    public function test_two_factor_challenge_expires_and_restarts_from_login(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor', absolute: false));

        \Illuminate\Support\Facades\Cache::forget('login_2fa:'.$user->id);

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
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'mixedcase@example.com',
        ]);
        $code = null;

        $this->post('/login', [
            'email' => 'MixedCase@Example.com',
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor', absolute: false));

        Mail::assertSent(LoginVerificationMail::class, function (LoginVerificationMail $mail) use (&$code, $user) {
            $code = $mail->code;

            return $mail->hasTo($user->email);
        });

        $this->post('/login/two-factor', [
            'code' => $code,
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
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

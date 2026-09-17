<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\AuthFailureStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthFailureLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);
    }

    public function test_invalid_password_is_logged_with_stage_and_flow(): void
    {
        Log::fake(['auth_security']);

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('password');

        Log::channel('auth_security')->assertLogged(function ($log) use ($user) {
            return $log->level === 'info'
                && $log->message === 'Auth failure.'
                && ($log->context['guard'] ?? null) === 'web'
                && ($log->context['flow'] ?? null) === 'credentials'
                && ($log->context['stage'] ?? null) === AuthFailureStage::PASSWORD_INVALID
                && ($log->context['email'] ?? null) === strtolower($user->email)
                && ($log->context['subject_id'] ?? null) === $user->id;
        });
    }

    public function test_unknown_email_is_logged_as_email_not_found(): void
    {
        Log::fake(['auth_security']);

        $this->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        Log::channel('auth_security')->assertLogged(function ($log) {
            return ($log->context['stage'] ?? null) === AuthFailureStage::EMAIL_NOT_FOUND
                && ($log->context['flow'] ?? null) === 'credentials';
        });
    }

    public function test_invalid_two_factor_code_is_logged(): void
    {
        Log::fake(['auth_security']);

        $user = User::factory()->withEmailTwoFactor()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login.two-factor', absolute: false));

        $this->post('/login/two-factor', [
            'code' => '000000',
        ])->assertSessionHasErrors('code');

        Log::channel('auth_security')->assertLogged(function ($log) use ($user) {
            return ($log->context['flow'] ?? null) === 'two_factor'
                && ($log->context['stage'] ?? null) === AuthFailureStage::TWO_FACTOR_CODE_INVALID
                && ($log->context['subject_id'] ?? null) === $user->id;
        });
    }
}

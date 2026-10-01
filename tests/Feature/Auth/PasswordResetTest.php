<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
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

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->get('http://coin.test/forgot-password')
            ->assertOk()
            ->assertSee(__('coin.auth.forgot_title'), false)
            ->assertDontSee('application-logo', false);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('http://coin.test/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_uses_branded_layout(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('http://coin.test/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $this->get('http://coin.test/reset-password/'.$notification->token)
                ->assertOk()
                ->assertSee(__('coin.auth.reset_title'), false)
                ->assertDontSee('figtree', false);

            return true;
        });
    }

    public function test_password_reset_notification_uses_branded_mailable(): void
    {
        Notification::fake();

        $user = User::factory()->create(['name' => 'Roman']);

        $this->post('http://coin.test/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mailable = $notification->toMail($user);

            $this->assertInstanceOf(PasswordResetMail::class, $mailable);
            $mailable->assertSeeInHtml('Password reset');
            $mailable->assertSeeInHtml('Reset password');
            $mailable->assertDontSeeInHtml('Hello!');

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('http://coin.test/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('http://coin.test/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }
}

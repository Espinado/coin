<?php

namespace Tests\Feature;

use App\Mail\AdminLoginVerificationMail;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
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

    public function test_admin_login_requires_email_code(): void
    {
        Mail::fake();

        Admin::query()->create([
            'name' => 'Staff',
            'email' => 'staff@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->post('http://admin.coin.test/login', [
            'email' => 'staff@coin.test',
            'password' => 'secret1234',
        ])->assertRedirect('http://admin.coin.test/login/two-factor');

        $this->assertGuest('admin');

        $code = null;

        Mail::assertSent(AdminLoginVerificationMail::class, function (AdminLoginVerificationMail $mail) use (&$code) {
            $code = $mail->code;

            return $mail->hasTo('staff@coin.test');
        });

        $this->post('http://admin.coin.test/login/two-factor', [
            'code' => $code,
        ])->assertRedirect('http://admin.coin.test/dashboard');

        $this->assertAuthenticated('admin');
    }

    public function test_admin_dashboard_requires_completed_two_factor(): void
    {
        Mail::fake();

        Admin::query()->create([
            'name' => 'Staff',
            'email' => 'staff@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->post('http://admin.coin.test/login', [
            'email' => 'staff@coin.test',
            'password' => 'secret1234',
        ]);

        $this->get('http://admin.coin.test/dashboard')
            ->assertRedirect('http://admin.coin.test/login');
    }
}

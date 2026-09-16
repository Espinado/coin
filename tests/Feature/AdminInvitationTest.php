<?php

namespace Tests\Feature;

use App\Mail\AdminInvitationMail;
use App\Mail\AdminLoginVerificationMail;
use App\Models\Admin;
use App\Models\AdminInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminInvitationTest extends TestCase
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

    public function test_admin_register_route_remains_blocked(): void
    {
        $this->get('http://admin.coin.test/register')->assertNotFound();

        $this->post('http://admin.coin.test/register', [
            'name' => 'Hacker',
            'email' => 'hacker@coin.test',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ])->assertNotFound();
    }

    public function test_admin_can_invite_new_administrator(): void
    {
        Mail::fake();

        $admin = Admin::query()->create([
            'name' => 'Owner',
            'email' => 'owner@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/admins/invite', [
                'email' => 'newadmin@coin.test',
                'name' => 'New Admin',
            ]);

        $response->assertRedirect('http://admin.coin.test/admins');

        $this->assertDatabaseHas('admin_invitations', [
            'email' => 'newadmin@coin.test',
            'name' => 'New Admin',
            'accepted_at' => null,
        ]);

        Mail::assertSent(AdminInvitationMail::class, function (AdminInvitationMail $mail) {
            return $mail->hasTo('newadmin@coin.test');
        });
    }

    public function test_invited_admin_can_accept_invitation_and_sign_in(): void
    {
        Mail::fake();

        $inviter = Admin::query()->create([
            'name' => 'Owner',
            'email' => 'owner@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $plainToken = str_repeat('a', 64);

        AdminInvitation::query()->create([
            'email' => 'staff@coin.test',
            'name' => 'Staff',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by_admin_id' => $inviter->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->get("http://admin.coin.test/invite/{$plainToken}")
            ->assertOk()
            ->assertSee('staff@coin.test');

        $response = $this->post("http://admin.coin.test/invite/{$plainToken}", [
            'name' => 'Staff Admin',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

        $response->assertRedirect('http://admin.coin.test/login/two-factor');

        $this->assertDatabaseHas('admins', [
            'email' => 'staff@coin.test',
            'name' => 'Staff Admin',
        ]);

        $code = null;

        Mail::assertSent(AdminLoginVerificationMail::class, function (AdminLoginVerificationMail $mail) use (&$code) {
            $code = $mail->code;

            return $mail->hasTo('staff@coin.test');
        });

        $this->post('http://admin.coin.test/login/two-factor', [
            'code' => $code,
        ])->assertRedirect('http://admin.coin.test/dashboard');

        $this->assertAuthenticatedAs(
            Admin::query()->where('email', 'staff@coin.test')->first(),
            'admin',
        );
    }

    public function test_expired_invitation_is_rejected(): void
    {
        $inviter = Admin::query()->create([
            'name' => 'Owner',
            'email' => 'owner@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $plainToken = str_repeat('b', 64);

        AdminInvitation::query()->create([
            'email' => 'late@coin.test',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by_admin_id' => $inviter->id,
            'expires_at' => now()->subMinute(),
        ]);

        $this->get("http://admin.coin.test/invite/{$plainToken}")
            ->assertRedirect('http://admin.coin.test/login');
    }

    public function test_inviting_existing_admin_email_sends_password_reset(): void
    {
        Mail::fake();

        $admin = Admin::query()->create([
            'name' => 'Owner',
            'email' => 'owner@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $existing = Admin::query()->create([
            'name' => 'Existing',
            'email' => 'existing@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/admins/invite', [
                'email' => 'existing@coin.test',
            ])
            ->assertRedirect('http://admin.coin.test/admins')
            ->assertSessionHas('status_type', 'success');

        $this->assertDatabaseHas('admin_invitations', [
            'email' => 'existing@coin.test',
            'admin_id' => $existing->id,
            'accepted_at' => null,
        ]);

        Mail::assertSent(AdminInvitationMail::class, function (AdminInvitationMail $mail) {
            return $mail->hasTo('existing@coin.test')
                && $mail->invitation->isPasswordReset();
        });
    }

    public function test_forgot_password_sends_reset_invitation(): void
    {
        Mail::fake();

        $admin = Admin::query()->create([
            'name' => 'Staff',
            'email' => 'staff@coin.test',
            'password' => Hash::make('old-password'),
        ]);

        $this->get('http://admin.coin.test/forgot-password')->assertOk();

        $this->post('http://admin.coin.test/forgot-password', [
            'email' => 'staff@coin.test',
        ])->assertRedirect('http://admin.coin.test/login');

        $this->assertDatabaseHas('admin_invitations', [
            'email' => 'staff@coin.test',
            'admin_id' => $admin->id,
        ]);

        Mail::assertSent(AdminInvitationMail::class);
    }

    public function test_password_reset_invitation_updates_password(): void
    {
        Mail::fake();

        $admin = Admin::query()->create([
            'name' => 'Staff',
            'email' => 'staff@coin.test',
            'password' => Hash::make('old-password'),
        ]);

        $plainToken = str_repeat('c', 64);

        AdminInvitation::query()->create([
            'email' => 'staff@coin.test',
            'name' => 'Staff',
            'admin_id' => $admin->id,
            'token_hash' => hash('sha256', $plainToken),
            'invited_by_admin_id' => $admin->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->post("http://admin.coin.test/invite/{$plainToken}", [
            'name' => 'Staff Updated',
            'password' => 'new-password1234',
            'password_confirmation' => 'new-password1234',
        ])->assertRedirect('http://admin.coin.test/login/two-factor');

        $code = null;

        Mail::assertSent(AdminLoginVerificationMail::class, function (AdminLoginVerificationMail $mail) use (&$code) {
            $code = $mail->code;

            return $mail->hasTo('staff@coin.test');
        });

        $this->post('http://admin.coin.test/login/two-factor', [
            'code' => $code,
        ])->assertRedirect('http://admin.coin.test/dashboard');

        $admin->refresh();

        $this->assertSame('Staff Updated', $admin->name);
        $this->assertTrue(Hash::check('new-password1234', $admin->password));
        $this->assertSame(1, Admin::query()->where('email', 'staff@coin.test')->count());
    }

    public function test_admin_cannot_delete_self_or_last_admin(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Only One',
            'email' => 'only@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->actingAs($admin, 'admin')
            ->delete("http://admin.coin.test/admins/{$admin->id}")
            ->assertRedirect('http://admin.coin.test/admins')
            ->assertSessionHas('status_type', 'error');

        $this->assertDatabaseHas('admins', ['id' => $admin->id]);
    }
}

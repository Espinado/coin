<?php

namespace Tests\Feature;

use App\Events\UserNotificationCreated;
use App\Mail\UserEventNotificationMail;
use App\Models\Admin;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserDirectNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(AdminSeeder::class);
        $this->admin = Admin::query()->firstOrFail();
    }

    public function test_admin_can_send_notification_to_single_user(): void
    {
        Event::fake([UserNotificationCreated::class]);
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->post("http://admin.coin.test/users/{$user->id}/notifications", [
                'notification_title' => 'Personal notice',
                'notification_body' => 'Please review your account.',
            ]);

        $response->assertRedirect(route('admin.users.show', $user, absolute: false));

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'title' => 'Personal notice',
            'body' => 'Please review your account.',
        ]);

        Event::assertDispatched(UserNotificationCreated::class, 1);
        Mail::assertSent(UserEventNotificationMail::class, fn (UserEventNotificationMail $mail) => $mail->hasTo($user->email));
    }

    public function test_direct_notification_creates_single_recipient_broadcast(): void
    {
        Event::fake([UserNotificationCreated::class]);
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->post("http://admin.coin.test/users/{$user->id}/notifications", [
                'notification_title' => 'Hello',
                'notification_body' => 'World',
            ]);

        $notification = UserNotification::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($notification);
        $this->assertSame(1, $notification->platformBroadcast?->recipients_count);
    }
}

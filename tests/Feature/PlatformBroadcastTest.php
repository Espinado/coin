<?php

namespace Tests\Feature;

use App\Events\UserNotificationCreated;
use App\Models\Admin;
use App\Models\PlatformBroadcast;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\PlatformBroadcastService;
use App\Services\UserInAppNotificationService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use App\Livewire\Dashboard;
use Tests\TestCase;

class PlatformBroadcastTest extends TestCase
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
        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(PlanSeeder::class);

        $this->admin = Admin::query()->firstOrFail();
    }

    public function test_admin_can_send_broadcast_to_active_users(): void
    {
        Event::fake([UserNotificationCreated::class]);

        User::factory()->count(2)->create(['is_blocked' => false]);
        User::factory()->create(['is_blocked' => true]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post('http://admin.coin.test/broadcasts', [
                'title' => 'Platform update',
                'body' => 'Scheduled maintenance tonight.',
            ]);

        $broadcast = PlatformBroadcast::query()->first();

        $response->assertRedirect(route('admin.broadcasts.show', $broadcast, absolute: false));

        $this->assertSame(2, UserNotification::query()->count());
        $this->assertSame(2, $broadcast?->recipients_count);

        Event::assertDispatched(UserNotificationCreated::class, 2);
    }

    public function test_user_marks_notification_read_when_opened(): void
    {
        $user = User::factory()->create();
        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'platform_broadcast_id' => PlatformBroadcast::query()->create([
                'admin_id' => $this->admin->id,
                'title' => 'Hello',
                'body' => 'World',
                'recipients_count' => 1,
            ])->id,
            'title' => 'Hello',
            'body' => 'World',
        ]);

        $this->assertSame(1, app(UserInAppNotificationService::class)->unreadCountForUser($user->id));

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('openNotification', $notification->id)
            ->assertSet('selectedNotificationId', $notification->id)
            ->assertSet('section', 8);

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, app(UserInAppNotificationService::class)->unreadCountForUser($user->id));
    }

    public function test_broadcast_service_dispatches_per_recipient(): void
    {
        Event::fake([UserNotificationCreated::class]);

        $users = User::factory()->count(3)->create(['is_blocked' => false]);

        app(PlatformBroadcastService::class)->send(
            $this->admin,
            'Notice',
            'Important information',
        );

        $this->assertSame($users->count(), UserNotification::query()->count());
        Event::assertDispatched(UserNotificationCreated::class, $users->count());
    }
}

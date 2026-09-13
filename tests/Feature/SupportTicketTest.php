<?php

namespace Tests\Feature;

use App\Events\SupportTicketMessageSent;
use App\Events\SupportTicketUpdated;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupportTicketTest extends TestCase
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

    public function test_user_can_create_and_view_own_ticket(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'Need help with withdrawal',
            SupportTicket::CATEGORY_WITHDRAWAL,
            'My withdrawal has been pending for two days.',
        );

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'subject' => 'Need help with withdrawal',
        ]);

        $this->assertDatabaseCount('support_ticket_messages', 1);
    }

    public function test_user_cannot_access_another_users_ticket_via_service(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ticket = app(SupportTicketService::class)->createForUser(
            $owner,
            'Private issue',
            SupportTicket::CATEGORY_ACCOUNT,
            'This ticket belongs to someone else.',
        );

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(SupportTicketService::class)->addUserMessage(
            $ticket,
            $other,
            'Trying to hijack ticket',
        );
    }

    public function test_admin_can_reply_to_ticket(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->create([
            'name' => 'Support Admin',
            'email' => 'support-admin@coin.test',
            'password' => Hash::make('password'),
        ]);

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'Contract question',
            SupportTicket::CATEGORY_CONTRACT,
            'When does my Core contract renew?',
        );

        $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/support/'.$ticket->id.'/reply', [
                'body' => 'Your contract renews automatically unless cancelled.',
                'status' => SupportTicket::STATUS_PENDING,
            ])
            ->assertRedirect(route('admin.support.show', $ticket, absolute: false));

        $this->assertDatabaseCount('support_ticket_messages', 2);
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => SupportTicket::STATUS_PENDING,
            'assigned_admin_id' => $admin->id,
        ]);
    }

    public function test_support_messages_dispatch_realtime_events(): void
    {
        Event::fake([
            SupportTicketMessageSent::class,
            SupportTicketUpdated::class,
        ]);

        $user = User::factory()->create();
        $admin = Admin::query()->create([
            'name' => 'Support Admin',
            'email' => 'realtime-admin@coin.test',
            'password' => Hash::make('password'),
        ]);

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'Realtime check',
            SupportTicket::CATEGORY_OTHER,
            'First message',
        );

        Event::assertDispatched(SupportTicketMessageSent::class);

        app(SupportTicketService::class)->addAdminMessage(
            $ticket,
            $admin,
            'Admin reply',
            SupportTicket::STATUS_PENDING,
        );

        Event::assertDispatchedTimes(SupportTicketMessageSent::class, 2);

        app(SupportTicketService::class)->updateStatus(
            $ticket,
            SupportTicket::STATUS_CLOSED,
            $admin,
        );

        Event::assertDispatched(SupportTicketUpdated::class);
    }

    public function test_regular_user_cannot_open_admin_support_pages(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'Help',
            SupportTicket::CATEGORY_OTHER,
            'Need assistance please.',
        );

        $this->get('http://admin.coin.test/support')
            ->assertRedirect('http://admin.coin.test/login');

        $this->actingAs($user)
            ->get('http://admin.coin.test/support')
            ->assertRedirect('http://admin.coin.test/login');
    }
}

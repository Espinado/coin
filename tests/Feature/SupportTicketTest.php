<?php

namespace Tests\Feature;

use App\Events\SupportTicketMessageSent;
use App\Events\SupportTicketUpdated;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportGuestSession;
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

    public function test_admin_unread_counts_and_mark_read_on_view(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->create([
            'name' => 'Support Admin',
            'email' => 'unread-admin@coin.test',
            'password' => Hash::make('password'),
        ]);

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'Unread check',
            SupportTicket::CATEGORY_OTHER,
            'First user message',
        );

        app(SupportTicketService::class)->addUserMessage(
            $ticket->fresh(),
            $user,
            'Second user message',
        );

        $this->assertSame(2, SupportTicket::totalUnreadForAdmin());
        $this->assertSame(2, $ticket->fresh(['messages'])->unreadMessagesForAdmin());

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/support/'.$ticket->id)
            ->assertOk();

        $this->assertSame(0, SupportTicket::totalUnreadForAdmin());
        $this->assertNotNull($ticket->fresh()->admin_last_read_at);
    }

    public function test_admin_nav_shows_total_unread_support_count(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->create([
            'name' => 'Support Admin',
            'email' => 'nav-unread-admin@coin.test',
            'password' => Hash::make('password'),
        ]);

        app(SupportTicketService::class)->createForUser(
            $user,
            'Nav badge check',
            SupportTicket::CATEGORY_OTHER,
            'Need help please',
        );

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/support')
            ->assertOk()
            ->assertSee('class="admin-support-badge"', false)
            ->assertSee('>1<', false);
    }

    public function test_guest_can_create_support_ticket_with_email(): void
    {
        $ticket = app(SupportTicketService::class)->createForGuest(
            'guest@example.com',
            'Pricing question',
            SupportTicket::CATEGORY_OTHER,
            'How much does the Core plan cost?',
        );

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'user_id' => null,
            'guest_email' => 'guest@example.com',
            'subject' => 'Pricing question',
        ]);

        $this->assertTrue($ticket->isGuest());
        $this->assertSame('guest@example.com', SupportGuestSession::current()?->guest_email);

        $token = SupportGuestSession::token();
        $this->assertNotNull($token);

        app(SupportTicketService::class)->addGuestMessage(
            $ticket->fresh(),
            $token,
            'Follow-up from guest',
        );

        $this->assertDatabaseCount('support_ticket_messages', 2);
        $this->assertSame(2, SupportTicket::totalUnreadForAdmin());
    }

    public function test_guest_livewire_create_ticket_requires_turnstile_when_enabled(): void
    {
        config([
            'coin.turnstile.enabled' => true,
            'coin.turnstile.site_key' => 'test-site',
            'coin.turnstile.secret_key' => 'test-secret',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'challenges.cloudflare.com/*' => \Illuminate\Support\Facades\Http::response(['success' => false], 200),
        ]);

        \Livewire\Livewire::test(\App\Livewire\GuestSupportChat::class)
            ->set('guestEmail', 'guest@example.com')
            ->set('newSubject', 'Need help please')
            ->set('newCategory', SupportTicket::CATEGORY_OTHER)
            ->set('newBody', 'This is a long enough message for support.')
            ->set('turnstileToken', 'fake-token')
            ->call('createTicket')
            ->assertHasErrors(['turnstileToken']);

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_guest_livewire_create_ticket_rejects_disposable_email(): void
    {
        config([
            'coin.turnstile.enabled' => false,
        ]);

        \Livewire\Livewire::test(\App\Livewire\GuestSupportChat::class)
            ->set('guestEmail', 'spam@mx-mailsrv.com')
            ->set('newSubject', 'Need help please')
            ->set('newCategory', SupportTicket::CATEGORY_OTHER)
            ->set('newBody', 'This is a long enough message for support.')
            ->call('createTicket')
            ->assertHasErrors(['guestEmail']);

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_guest_livewire_create_ticket_succeeds_with_valid_turnstile(): void
    {
        config([
            'coin.turnstile.enabled' => true,
            'coin.turnstile.site_key' => 'test-site',
            'coin.turnstile.secret_key' => 'test-secret',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'challenges.cloudflare.com/*' => \Illuminate\Support\Facades\Http::response(['success' => true], 200),
        ]);

        \Livewire\Livewire::test(\App\Livewire\GuestSupportChat::class)
            ->set('guestEmail', 'guest@example.com')
            ->set('newSubject', 'Need help please')
            ->set('newCategory', SupportTicket::CATEGORY_OTHER)
            ->set('newBody', 'This is a long enough message for support.')
            ->set('turnstileToken', 'valid-token')
            ->call('createTicket')
            ->assertHasNoErrors()
            ->assertSet('ticketId', fn ($id) => is_int($id) && $id > 0);

        $this->assertDatabaseHas('support_tickets', [
            'guest_email' => 'guest@example.com',
            'subject' => 'Need help please',
        ]);
    }

    public function test_guest_livewire_create_ticket_skips_turnstile_when_disabled(): void
    {
        config([
            'coin.turnstile.enabled' => false,
        ]);

        \Livewire\Livewire::test(\App\Livewire\GuestSupportChat::class)
            ->set('guestEmail', 'guest@example.com')
            ->set('newSubject', 'Need help please')
            ->set('newCategory', SupportTicket::CATEGORY_OTHER)
            ->set('newBody', 'This is a long enough message for support.')
            ->call('createTicket')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('support_tickets', 1);
    }

    public function test_guest_channel_access_requires_db_matched_token(): void
    {
        $ticket = app(SupportTicketService::class)->createForGuest(
            'guest-auth@example.com',
            'Auth check',
            SupportTicket::CATEGORY_OTHER,
            'Please verify guest channel access.',
        );

        $this->assertTrue(SupportGuestSession::canAccessTicket($ticket->id));

        session(['guest_support' => [
            'ticket_id' => $ticket->id,
            'token' => 'tampered-not-in-database',
        ]]);

        $this->assertFalse(SupportGuestSession::canAccessTicket($ticket->id));
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

    public function test_admin_missing_support_ticket_redirects_to_index(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Support Admin',
            'email' => 'missing-ticket-admin@coin.test',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/support/999999')
            ->assertRedirect(route('admin.support.index', absolute: false))
            ->assertSessionHas('status', __('coin.admin.support_ticket_not_found'));
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Admin;
use App\Models\Plan;
use App\Models\PlanOffer;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\DepositService;
use App\Services\PlanChangeRequestService;
use App\Services\PlanPurchaseService;
use App\Services\PrivateOfferService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrivateOfferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.deposits.auto_confirm_mock' => true,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(PlanSeeder::class);
    }

    public function test_quote_preview_formula(): void
    {
        $quote = app(PrivateOfferService::class)->quotePreview(10_000, 365, 18.25);

        $this->assertSame(10_000.0, $quote['amount']);
        $this->assertSame(365, $quote['days']);
        $this->assertSame(18.25, $quote['apr']);
        $this->assertSame(5.0, $quote['daily']);
        $this->assertSame(1825.0, $quote['total']);
    }

    public function test_contact_sales_opens_enterprise_ticket(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('contactEnterpriseSales')
            ->assertSet('section', 7);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'category' => SupportTicket::CATEGORY_ENTERPRISE,
            'status' => SupportTicket::STATUS_OPEN,
        ]);
    }

    public function test_contact_sales_reuses_open_enterprise_ticket(): void
    {
        $user = User::factory()->create();
        $existing = SupportTicket::query()->create([
            'user_id' => $user->id,
            'reference' => 'SUP-TEST001',
            'subject' => 'Existing',
            'category' => SupportTicket::CATEGORY_ENTERPRISE,
            'status' => SupportTicket::STATUS_OPEN,
            'last_reply_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('contactEnterpriseSales')
            ->assertSet('selectedTicketId', $existing->id);

        $this->assertSame(1, SupportTicket::query()->where('user_id', $user->id)->where('category', SupportTicket::CATEGORY_ENTERPRISE)->count());
    }

    public function test_offer_visible_only_to_assigned_user(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Offer Admin',
            'email' => 'offer-admin@test.lv',
            'password' => 'secret',
        ]);
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $offer = app(PrivateOfferService::class)->create($admin, $owner, 12_000, 120, 16.5, null, 48);

        $ownerPlans = app(\App\Services\DashboardDataService::class)->forUser($owner->fresh())['plans'];
        $otherPlans = app(\App\Services\DashboardDataService::class)->forUser($other->fresh())['plans'];

        $this->assertTrue($ownerPlans->contains('id', $offer->plan_id));
        $this->assertFalse($otherPlans->contains('id', $offer->plan_id));
        $this->assertFalse(
            Plan::query()->publicCatalog()->whereKey($offer->plan_id)->exists()
        );
    }

    public function test_expired_offer_cannot_be_purchased(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Expire Admin',
            'email' => 'expire-admin@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $offer = app(PrivateOfferService::class)->create($admin, $user, 5_000, 90, 14, null, 48);
        $offer->update(['expires_at' => now()->subMinute()]);

        app(DepositService::class)->createPending($user, 10_000);

        $this->expectException(\RuntimeException::class);
        app(PlanPurchaseService::class)->purchase($user->fresh(), $offer->plan->fresh(), 5_000);
    }

    public function test_purchase_accepts_exact_amount_and_closes_offer(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Buy Admin',
            'email' => 'buy-admin@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $offer = app(PrivateOfferService::class)->create($admin, $user, 8_000, 180, 17, null, 72);

        app(DepositService::class)->createPending($user, 20_000);
        $contract = app(PlanPurchaseService::class)->purchase($user->fresh(), $offer->plan->fresh(), 8_000);

        $this->assertSame(8_000.0, (float) $contract->principal_amount);
        $this->assertSame(PlanOffer::STATUS_ACCEPTED, $offer->fresh()->status);
        $this->assertFalse((bool) $offer->plan->fresh()->is_active);
        $this->assertSame(Plan::OFFER_STATUS_ACCEPTED, $offer->plan->fresh()->offer_status);
    }

    public function test_plan_change_rejects_private_plans(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Change Admin',
            'email' => 'change-admin@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $offer = app(PrivateOfferService::class)->create($admin, $user, 9_000, 100, 15, null, 48);

        app(DepositService::class)->createPending($user, 20_000);
        $contract = app(PlanPurchaseService::class)->purchase($user->fresh(), $core, 1_100);

        $this->expectException(\RuntimeException::class);
        app(PlanChangeRequestService::class)->createRequest($user->fresh(), $contract->fresh(), $offer->plan->fresh());
    }
}

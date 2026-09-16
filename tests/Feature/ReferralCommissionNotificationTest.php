<?php

namespace Tests\Feature;

use App\Events\ReferralCommissionPaid;
use App\Mail\UserEventNotificationMail;
use App\Models\Plan;
use App\Models\ReferralProfile;
use App\Models\User;
use App\Services\DepositService;
use App\Services\PlanPurchaseService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReferralCommissionNotificationTest extends TestCase
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

    public function test_referral_purchase_sends_email_and_broadcasts_realtime_event(): void
    {
        Event::fake([ReferralCommissionPaid::class]);
        Mail::fake();

        $referrer = User::factory()->create([
            'notify_referral_activity' => false,
        ]);

        ReferralProfile::query()->create([
            'user_id' => $referrer->id,
            'code' => 'COIN-REFRT',
            'level1_percent' => 20,
            'level2_percent' => 0,
        ]);

        $buyer = User::factory()->create([
            'referred_by_user_id' => $referrer->id,
        ]);

        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        app(DepositService::class)->createPending($buyer, 2000);
        app(PlanPurchaseService::class)->purchase($buyer, $core, 1100);

        Mail::assertSent(UserEventNotificationMail::class, function (UserEventNotificationMail $mail) use ($referrer): bool {
            return $mail->hasTo($referrer->email);
        });

        Event::assertDispatched(ReferralCommissionPaid::class, function (ReferralCommissionPaid $event) use ($referrer): bool {
            return (int) $event->commission->referrer_user_id === (int) $referrer->id
                && $event->source === ReferralCommissionPaid::SOURCE_PURCHASE
                && $event->payoutAmount === 220.0;
        });
    }

    public function test_referral_upgrade_sends_email_and_broadcasts_realtime_event(): void
    {
        Event::fake([ReferralCommissionPaid::class]);
        Mail::fake();

        $referrer = User::factory()->create([
            'notify_referral_activity' => false,
        ]);

        ReferralProfile::query()->create([
            'user_id' => $referrer->id,
            'code' => 'COIN-REFUP',
            'level1_percent' => 20,
            'level2_percent' => 0,
        ]);

        $buyer = User::factory()->create([
            'referred_by_user_id' => $referrer->id,
        ]);

        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($buyer, 5000);
        $contract = app(PlanPurchaseService::class)->purchase($buyer, $core, 1100);

        Mail::fake();
        Event::fake([ReferralCommissionPaid::class]);

        app(PlanPurchaseService::class)->changePlan($buyer->fresh(), $contract->fresh(['plan']), $cluster);

        Mail::assertSent(UserEventNotificationMail::class, function (UserEventNotificationMail $mail) use ($referrer): bool {
            return $mail->hasTo($referrer->email);
        });

        Event::assertDispatched(ReferralCommissionPaid::class, function (ReferralCommissionPaid $event) use ($referrer): bool {
            return (int) $event->commission->referrer_user_id === (int) $referrer->id
                && $event->source === ReferralCommissionPaid::SOURCE_UPGRADE
                && $event->payoutAmount === 460.0;
        });
    }
}

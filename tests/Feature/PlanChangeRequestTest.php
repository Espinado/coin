<?php

namespace Tests\Feature;

use App\Mail\UserEventNotificationMail;
use App\Models\Admin;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Models\User;
use App\Services\DepositService;
use App\Services\PlanChangeRequestService;
use App\Services\PlanPurchaseService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlanChangeRequestTest extends TestCase
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

    public function test_user_can_create_pending_plan_change_request_and_hold_top_up(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($user, 5000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $request = app(PlanChangeRequestService::class)->createRequest($user->fresh(), $contract->fresh(['plan']), $cluster);

        $user->refresh();

        $this->assertSame(PlanChangeRequest::STATUS_PENDING, $request->status);
        $this->assertSame('2300.00', number_format((float) $request->top_up_amount, 2, '.', ''));
        $this->assertSame('1800.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame('2300.00', number_format((float) $user->wallet->pending, 2, '.', ''));
    }

    public function test_admin_approval_applies_plan_change_and_releases_hold(): void
    {
        Mail::fake();

        $admin = Admin::query()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($user, 5000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $request = app(PlanChangeRequestService::class)->createRequest($user->fresh(), $contract->fresh(['plan']), $cluster);

        $approved = app(PlanChangeRequestService::class)->approve($request->fresh(), $admin);

        $user->refresh();
        $contract->refresh();

        $this->assertSame(PlanChangeRequest::STATUS_APPROVED, $approved->status);
        $this->assertSame($cluster->id, $contract->plan_id);
        $this->assertSame('3400.00', number_format((float) $contract->principal_amount, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $user->wallet->pending, 2, '.', ''));
        $this->assertSame('1800.00', number_format((float) $user->wallet->available, 2, '.', ''));

        Mail::assertSent(UserEventNotificationMail::class, function (UserEventNotificationMail $mail) use ($user, $approved): bool {
            return $mail->hasTo($user->email)
                && str_contains($mail->subjectLine, 'изменение плана')
                && str_contains(implode("\n", $mail->lines), $approved->reference);
        });
    }

    public function test_admin_rejection_returns_reserved_top_up(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Test Admin',
            'email' => 'admin2@test.lv',
            'password' => 'secret',
        ]);
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($user, 5000);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $request = app(PlanChangeRequestService::class)->createRequest($user->fresh(), $contract->fresh(['plan']), $cluster);

        app(PlanChangeRequestService::class)->reject($request->fresh(), $admin);

        $user->refresh();
        $contract->refresh();

        $this->assertSame($core->id, $contract->plan_id);
        $this->assertSame('4100.00', number_format((float) $user->wallet->available, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $user->wallet->pending, 2, '.', ''));
    }

    public function test_cannot_create_request_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $core = Plan::query()->where('slug', 'core')->firstOrFail();
        $cluster = Plan::query()->where('slug', 'cluster')->firstOrFail();

        app(DepositService::class)->createPending($user, 1200);
        $contract = app(PlanPurchaseService::class)->purchase($user, $core, 1100);

        $this->expectException(\RuntimeException::class);

        app(PlanChangeRequestService::class)->createRequest($user->fresh(), $contract->fresh(['plan']), $cluster);
    }
}

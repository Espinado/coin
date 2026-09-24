<?php

namespace Tests\Feature;

use App\Jobs\ExpirePendingDepositJob;
use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use App\Services\DepositService;
use App\Services\PlatformSettingsService;
use App\Support\PaymentStatusReason;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DepositExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'ccapi',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(AdminSeeder::class);
        $this->seed(PlatformSettingsSeeder::class);
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => true]);
    }

    public function test_expire_if_due_rejects_expired_pending_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TExpiredAddress',
            'gateway_network' => 'trx',
            'expires_at' => now()->subSecond(),
        ]);

        $result = app(DepositService::class)->expireIfDue($deposit);

        $this->assertNotNull($result);
        $this->assertSame(Deposit::STATUS_REJECTED, $result->status);
        $this->assertSame(PaymentStatusReason::DEPOSIT_EXPIRED, $result->status_reason);
    }

    public function test_expire_if_due_ignores_future_pending_deposit(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TActiveAddress',
            'gateway_network' => 'trx',
            'expires_at' => now()->addHour(),
        ]);

        $result = app(DepositService::class)->expireIfDue($deposit);

        $this->assertNull($result);
        $this->assertSame(Deposit::STATUS_PENDING, $deposit->fresh()->status);
    }

    public function test_apply_deposit_intent_schedules_expiry_job(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 50,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
        ]);

        $expiresAt = now()->addHour();

        app(DepositService::class)->applyDepositIntent(
            $deposit,
            new \App\Services\Payment\Dtos\DepositIntentDto(
                paymentAddress: 'TAddress123',
                gatewayUniqId: Deposit::gatewayUniqId($deposit->id),
                gatewayNetwork: 'trx',
                expiresAt: $expiresAt,
            ),
        );

        Bus::assertDispatched(ExpirePendingDepositJob::class, function (ExpirePendingDepositJob $job) use ($deposit) {
            return $job->depositId === $deposit->id;
        });
    }

    public function test_admin_status_endpoint_rejects_expired_deposit(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TExpiredAddress',
            'gateway_network' => 'trx',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.deposits.status', $deposit));

        $response
            ->assertOk()
            ->assertJson([
                'id' => $deposit->id,
                'status' => Deposit::STATUS_REJECTED,
            ]);
    }
}

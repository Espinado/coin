<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\Payment\Dtos\PayoutRequestDto;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\WithdrawalService;
use App\Support\PlatformTerms;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PayoutAddressTest;
use Tests\TestCase;

class WithdrawalConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public int $sendPayoutCalls = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'test-ccapi-key',
            'coin.payments.mock.auto_complete_payout' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->app->instance(PaymentGatewayInterface::class, new class($this) implements PaymentGatewayInterface
        {
            public function __construct(private WithdrawalConcurrencyTest $test) {}

            public function createDepositIntent(\App\Models\Deposit $deposit): \App\Services\Payment\Dtos\DepositIntentDto
            {
                throw new \RuntimeException('Not used in this test.');
            }

            public function sendPayout(Withdrawal $withdrawal): PayoutRequestDto
            {
                $this->test->sendPayoutCalls++;

                return new PayoutRequestDto(
                    gatewayRequestId: 'GW-'.$this->test->sendPayoutCalls,
                    gatewayUniqId: Withdrawal::gatewayUniqId($withdrawal->reference),
                    raw: ['test' => true],
                );
            }

            public function getPayoutStatus(Withdrawal $withdrawal): \App\Services\Payment\Dtos\PayoutStatusDto
            {
                throw new \RuntimeException('Not used in this test.');
            }

            public function verifyIpn(\Illuminate\Http\Request $request): \App\Services\Payment\Dtos\VerifiedIpnEvent
            {
                throw new \RuntimeException('Not used in this test.');
            }
        });
    }

    public function test_double_gateway_dispatch_sends_payout_only_once(): void
    {
        $admin = Admin::query()->firstOrFail();
        $withdrawal = $this->makeProcessingWithdrawal();

        $service = app(WithdrawalService::class);

        $service->dispatchViaGateway($withdrawal, $admin, false);
        $service->dispatchViaGateway($withdrawal->fresh(), $admin, false);

        $this->assertSame(1, $this->sendPayoutCalls);
        $this->assertSame('GW-1', $withdrawal->fresh()->gateway_request_id);
    }

    public function test_mark_paid_from_gateway_is_idempotent(): void
    {
        $withdrawal = $this->makeProcessingWithdrawal([
            'gateway_request_id' => 'GW-LOCKED',
        ]);

        $service = app(WithdrawalService::class);

        $service->markPaidFromGateway($withdrawal, 'tx-1', '7');
        $service->markPaidFromGateway($withdrawal->fresh(), 'tx-1', '7');

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->fresh()->status);
        $this->assertSame(1, WalletTransaction::query()
            ->where('user_id', $withdrawal->user_id)
            ->where('source', $withdrawal->reference)
            ->where('type', PlatformTerms::TX_PAYOUT)
            ->count());
    }

    public function test_mark_failed_from_gateway_is_idempotent(): void
    {
        $withdrawal = $this->makeProcessingWithdrawal([
            'gateway_request_id' => 'GW-FAIL',
        ]);

        $user = $withdrawal->user;
        $wallet = $user->wallet;
        $availableBefore = (float) $wallet->available;

        $service = app(WithdrawalService::class);

        $service->markFailedFromGateway($withdrawal, '8');
        $service->markFailedFromGateway($withdrawal->fresh(), '8');

        $wallet->refresh();

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->fresh()->status);
        $this->assertSame($availableBefore + 50.0, (float) $wallet->available);
    }

    public function test_second_admin_reject_of_pending_does_not_double_release(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();
        $wallet = app(\App\Services\WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 0,
            'balance' => 100,
            'pending' => 50,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-REJ'.random_int(1000, 9999),
            'amount' => 50,
            'base_amount' => 50,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
            'status' => Withdrawal::STATUS_PENDING,
        ]);

        $service = app(WithdrawalService::class);
        $service->updateStatus($withdrawal, Withdrawal::STATUS_REJECTED, $admin);

        $wallet->refresh();
        $this->assertSame(50.0, (float) $wallet->available);
        $this->assertSame(0.0, (float) $wallet->pending);

        // Same-status update is a no-op (does not re-release pending).
        $service->updateStatus($withdrawal->fresh(), Withdrawal::STATUS_REJECTED, $admin);

        $wallet->refresh();
        $this->assertSame(50.0, (float) $wallet->available);
        $this->assertSame(0.0, (float) $wallet->pending);
        $this->assertSame(100.0, (float) $wallet->balance);
        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->fresh()->status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeProcessingWithdrawal(array $overrides = []): Withdrawal
    {
        $user = User::factory()->create();
        $wallet = app(\App\Services\WalletService::class)->ensureWallet($user);
        $wallet->update([
            'available' => 0,
            'balance' => 50,
            'pending' => 50,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        return Withdrawal::query()->create(array_merge([
            'user_id' => $user->id,
            'reference' => 'WD-LOCK'.random_int(1000, 9999),
            'amount' => 50,
            'base_amount' => 50,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
            'status' => Withdrawal::STATUS_PROCESSING,
        ], $overrides));
    }
}

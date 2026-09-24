<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Models\Admin;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\Payment\CcapiIpnVerifier;
use App\Services\Payment\WithdrawalPollService;
use App\Services\PlatformSettingsService;
use App\Services\WithdrawalService;
use App\Support\PaymentStatusReason;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\PayoutAddressTest;
use Tests\TestCase;

class PaymentRejectionReasonTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'test-ccapi-key';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => $this->apiKey,
            'coin.payments.ccapi.min_confirmations' => 1,
        ]);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_deposit_ipn_with_wrong_amount_rejects_with_reason(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockAddress123',
            'gateway_network' => 'trx',
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'in',
            'date' => now()->timestamp,
            'from' => '',
            'to' => 'TMockAddress123',
            'token' => 'USDT',
            'amount' => '50.000000',
            'fee' => '0.000000',
            'txid' => 'wrong-amount-tx',
            'pos' => 0,
            'confirmation' => 1,
            'label' => Deposit::gatewayUniqId($deposit->id),
        ];
        $payload['sign'] = app(CcapiIpnVerifier::class)->sign($payload, $this->apiKey);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();

        $this->assertSame(Deposit::STATUS_REJECTED, $deposit->status);
        $this->assertSame(PaymentStatusReason::DEPOSIT_AMOUNT_MISMATCH, $deposit->status_reason);
        $this->assertStringContainsString('50.00', $deposit->userRejectionMessage() ?? '');
    }

    public function test_deposit_expired_ipn_rejects_with_reason(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
            'payment_address' => 'TMockAddress123',
            'gateway_network' => 'trx',
            'expires_at' => now()->subMinute(),
        ]);
        $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
        $deposit->refresh();

        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'in',
            'date' => now()->timestamp,
            'from' => '',
            'to' => 'TMockAddress123',
            'token' => 'USDT',
            'amount' => '100.000000',
            'fee' => '0.000000',
            'txid' => 'late-tx',
            'pos' => 0,
            'confirmation' => 1,
            'label' => Deposit::gatewayUniqId($deposit->id),
        ];
        $payload['sign'] = app(CcapiIpnVerifier::class)->sign($payload, $this->apiKey);

        $this->postJson('http://coin.test/webhooks/ccapi', $payload)->assertOk();

        $deposit->refresh();

        $this->assertSame(Deposit::STATUS_REJECTED, $deposit->status);
        $this->assertSame(PaymentStatusReason::DEPOSIT_EXPIRED, $deposit->status_reason);
    }

    public function test_deposit_service_reject_stores_reason_message(): void
    {
        $user = User::factory()->create();
        $deposit = Deposit::query()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'currency' => 'USDT',
            'status' => Deposit::STATUS_PENDING,
            'method' => 'ccapi',
        ]);

        $deposit = app(DepositService::class)->reject(
            $deposit,
            null,
            PaymentStatusReason::DEPOSIT_EXPIRED,
        );

        $this->assertSame(__('coin.payment_reasons.deposit_expired'), $deposit->userRejectionMessage());
    }

    public function test_gateway_failed_withdrawal_has_user_reason(): void
    {
        config(['coin.payments.ccapi.poll_stuck_withdrawals' => true]);
        app(PlatformSettingsService::class)->setMany(['payment_gate_enabled' => true]);

        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 450,
            'balance' => 450,
            'pending' => 0,
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-REJ0001',
            'amount' => 50,
            'base_amount' => 50,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient123',
            'gateway_request_id' => '999',
            'status' => Withdrawal::STATUS_PROCESSING,
        ]);

        Http::fake([
            '*status*' => Http::response(['result' => ['id' => '999', 'state' => '8']], 200),
        ]);

        app(WithdrawalPollService::class)->pollStuckWithdrawals();

        $withdrawal->refresh();

        $this->assertSame(Withdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertSame(PaymentStatusReason::WITHDRAWAL_GATEWAY_FAILED, $withdrawal->status_reason);
        $this->assertStringContainsString('8', $withdrawal->userRejectionMessage() ?? '');
    }

    public function test_admin_rejected_withdrawal_has_user_reason(): void
    {
        $user = User::factory()->create();
        $user->wallet->update([
            'available' => 100,
            'balance' => 100,
            'pending' => 50,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-REJ0002',
            'amount' => 50,
            'base_amount' => 50,
            'currency' => 'USDT',
            'withdrawal_type' => 'available_balance',
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'status' => Withdrawal::STATUS_PENDING,
        ]);

        $this->seed(AdminSeeder::class);
        $admin = Admin::query()->firstOrFail();

        app(WithdrawalService::class)->updateStatus(
            $withdrawal,
            Withdrawal::STATUS_REJECTED,
            $admin,
        );

        $this->assertSame(PaymentStatusReason::WITHDRAWAL_ADMIN_REJECTED, $withdrawal->fresh()->status_reason);
        $this->assertSame(
            __('coin.payment_reasons.withdrawal_admin_rejected'),
            $withdrawal->fresh()->userRejectionMessage(),
        );
    }
}

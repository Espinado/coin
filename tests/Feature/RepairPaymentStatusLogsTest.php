<?php

namespace Tests\Feature;

use App\Models\PaymentStatusLog;
use App\Models\PaymentWebhookLog;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairPaymentStatusLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_removes_ignored_poll_and_refreshes_gateway_failed_row(): void
    {
        $user = User::factory()->create();
        $withdrawal = Withdrawal::query()->create([
            'user_id' => $user->id,
            'reference' => 'WD-TEST001',
            'amount' => 10,
            'currency' => 'USDT',
            'base_amount' => 10,
            'withdrawal_type' => 'available_balance',
            'payout_address' => 'TRecipient1234567890123456789012345',
            'network_label' => 'TRC-20',
            'status' => Withdrawal::STATUS_REJECTED,
            'status_reason' => 'withdrawal_gateway_failed',
            'gateway_state' => '8',
        ]);

        $ignoredWebhook = PaymentWebhookLog::query()->create([
            'gateway' => 'ccapi',
            'event_type' => 'payout_poll',
            'withdrawal_id' => $withdrawal->id,
            'processing_result' => 'ignored: Awaiting payout confirmation (state 4).',
            'payload' => ['state' => '4', 'txid' => 'abc'],
        ]);

        PaymentStatusLog::query()->create([
            'entity_type' => 'withdrawal',
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $user->id,
            'reference' => $withdrawal->reference,
            'source' => PaymentStatusLog::SOURCE_POLL,
            'event_type' => 'payout_poll',
            'new_status' => Withdrawal::STATUS_REJECTED,
            'status_reason' => 'withdrawal_gateway_failed',
            'result' => 'ignored',
            'title' => 'Old pending title',
            'message' => 'State 4 — old message',
            'payload' => ['webhook_log_id' => $ignoredWebhook->id, 'state' => '4'],
            'gateway_state' => '4',
        ]);

        $failedWebhook = PaymentWebhookLog::query()->create([
            'gateway' => 'ccapi',
            'event_type' => 'payout_poll',
            'withdrawal_id' => $withdrawal->id,
            'processing_result' => 'processed: Gateway reported failed payout (state 8). Funds restored.',
            'payload' => ['state' => '8', 'result' => 'OUT_OF_ENERGY'],
            'created_at' => now()->subMinute(),
        ]);

        $failedLog = PaymentStatusLog::query()->create([
            'entity_type' => 'withdrawal',
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $user->id,
            'reference' => $withdrawal->reference,
            'source' => PaymentStatusLog::SOURCE_POLL,
            'event_type' => 'payout_poll',
            'new_status' => Withdrawal::STATUS_REJECTED,
            'status_reason' => 'withdrawal_gateway_failed',
            'result' => 'processed',
            'title' => 'Old failed title',
            'message' => 'State 8 — old message',
            'payload' => ['webhook_log_id' => $failedWebhook->id, 'state' => '8', 'result' => 'OUT_OF_ENERGY'],
            'gateway_state' => '8',
        ]);

        $this->artisan('payment-logs:repair')->assertSuccessful();

        $this->assertDatabaseMissing('payment_status_logs', [
            'payload->webhook_log_id' => $ignoredWebhook->id,
        ]);

        $failedLog->refresh();

        $this->assertSame(__('coin.payment_log.title_gateway_failed'), $failedLog->title);
        $this->assertStringContainsString('OUT_OF_ENERGY', $failedLog->message);
        $this->assertSame(Withdrawal::STATUS_PROCESSING, $failedLog->previous_status);
        $this->assertSame(Withdrawal::STATUS_REJECTED, $failedLog->new_status);
        $this->assertSame('withdrawal_gateway_failed', $failedLog->status_reason);
    }
}

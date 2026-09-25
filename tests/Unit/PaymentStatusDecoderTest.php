<?php

namespace Tests\Unit;

use App\Models\PaymentStatusLog;
use App\Models\Withdrawal;
use App\Support\PaymentStatusDecoder;
use Tests\TestCase;

class PaymentStatusDecoderTest extends TestCase
{
    public function test_poll_pending_message_explains_no_status_change(): void
    {
        $decoded = PaymentStatusDecoder::decodeWebhookMessage(
            'ignored: Awaiting payout confirmation (state 4).',
            [
                'state' => '4',
                'txid' => 'abc123',
            ],
        );

        $this->assertSame(__('coin.payment_log.title_poll_pending'), $decoded['title']);
        $this->assertStringContainsString('не менялся', mb_strtolower($decoded['message']));
        $this->assertStringContainsString('abc123', $decoded['message']);
    }

    public function test_gateway_failed_poll_message_mentions_funds_restored(): void
    {
        $decoded = PaymentStatusDecoder::decodeWebhookMessage(
            'processed: Gateway reported failed payout (state 8). Funds restored.',
            [
                'state' => '8',
                'result' => 'OUT_OF_ENERGY',
            ],
        );

        $this->assertSame(__('coin.payment_log.title_gateway_failed'), $decoded['title']);
        $this->assertStringContainsString('OUT_OF_ENERGY', $decoded['message']);
        $this->assertStringContainsString('баланс', mb_strtolower($decoded['message']));
    }

    public function test_poll_log_without_previous_status_is_not_a_transition(): void
    {
        $log = new PaymentStatusLog([
            'entity_type' => 'withdrawal',
            'event_type' => 'payout_poll',
            'previous_status' => null,
            'new_status' => 'rejected',
            'result' => 'ignored',
        ]);

        $this->assertFalse($log->hasStatusTransition());
        $this->assertSame('—', $log->transitionLabel());
    }

    public function test_journal_status_reason_label(): void
    {
        $label = PaymentStatusDecoder::journalStatusReasonLabel('withdrawal', 'withdrawal_gateway_failed');

        $this->assertSame(__('coin.payment_log.status_reason_withdrawal_gateway_failed'), $label);
    }

    public function test_entity_status_display_uses_current_status_only(): void
    {
        $withdrawal = new Withdrawal([
            'reference' => 'WD-STALE02',
            'status' => Withdrawal::STATUS_REJECTED,
        ]);

        $log = new PaymentStatusLog([
            'entity_type' => 'withdrawal',
            'withdrawal_id' => 1,
            'event_type' => 'created',
            'previous_status' => null,
            'new_status' => Withdrawal::STATUS_PENDING,
        ]);
        $log->setRelation('withdrawal', $withdrawal);

        $this->assertTrue($log->showsEntityStatus());
        $this->assertSame(__('coin.withdrawal_status.rejected'), $log->entityStatusDisplayLabel());
        $this->assertStringNotContainsString(__('coin.withdrawal_status.pending'), $log->entityStatusDisplayLabel());
    }
}

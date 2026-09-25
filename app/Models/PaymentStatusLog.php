<?php

namespace App\Models;

use App\Support\LocaleFormat;
use App\Support\PaymentStatusDecoder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentStatusLog extends Model
{
    public const UPDATED_AT = null;

    public const SOURCE_IPN = 'ipn';

    public const SOURCE_POLL = 'poll';

    public const SOURCE_APP = 'app';

    public const SOURCE_ADMIN = 'admin';

    public const SOURCE_SIMULATOR = 'simulator';

    protected $fillable = [
        'entity_type',
        'deposit_id',
        'withdrawal_id',
        'user_id',
        'reference',
        'source',
        'event_type',
        'previous_status',
        'new_status',
        'gateway',
        'gateway_state',
        'gateway_result',
        'status_reason',
        'result',
        'title',
        'message',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entityLabel(): string
    {
        return PaymentStatusDecoder::entityTypeLabel($this->entity_type);
    }

    public function sourceLabel(): string
    {
        return PaymentStatusDecoder::sourceLabel($this->source);
    }

    public function resultLabel(): string
    {
        return PaymentStatusDecoder::webhookResult($this->result, $this->event_type);
    }

    public function eventTypeLabel(): string
    {
        return PaymentStatusDecoder::eventTypeLabel($this->event_type);
    }

    public function kindLabel(): string
    {
        return PaymentStatusDecoder::logKindLabel($this->event_type, $this->result);
    }

    public function statusReasonLabel(): ?string
    {
        return PaymentStatusDecoder::journalStatusReasonLabel($this->entity_type, $this->status_reason);
    }

    public function hasStatusTransition(): bool
    {
        if (! PaymentStatusDecoder::hasStatusTransition($this->previous_status, $this->new_status)) {
            return false;
        }

        if ($this->previous_status === null && in_array($this->event_type, ['payout_poll', 'deposit_ipn', 'payout_ipn'], true)) {
            return false;
        }

        return true;
    }

    public function transitionLabel(): string
    {
        if (! $this->hasStatusTransition()) {
            return '—';
        }

        return PaymentStatusDecoder::transitionLabel(
            $this->previous_status,
            $this->new_status,
            $this->entity_type,
        );
    }

    public function entityStatusDisplayLabel(): string
    {
        $current = $this->currentEntityStatusLabel();

        if ($current !== null) {
            return $current;
        }

        if ($this->hasStatusTransition()) {
            return $this->transitionLabel();
        }

        return '—';
    }

    public function showsEntityStatus(): bool
    {
        return $this->currentEntityStatusLabel() !== null;
    }

    public function currentEntityStatusLabel(): ?string
    {
        $entity = $this->linkedEntity();

        if ($entity instanceof Withdrawal) {
            return $entity->statusLabel();
        }

        if ($entity instanceof Deposit) {
            return PaymentStatusDecoder::depositStatus($entity->status);
        }

        return null;
    }

    public function linkedEntity(): Deposit|Withdrawal|null
    {
        if ($this->relationLoaded('withdrawal') && $this->withdrawal !== null) {
            return $this->withdrawal;
        }

        if ($this->relationLoaded('deposit') && $this->deposit !== null) {
            return $this->deposit;
        }

        if ($this->withdrawal_id !== null) {
            return $this->withdrawal;
        }

        if ($this->deposit_id !== null) {
            return $this->deposit;
        }

        return null;
    }

    public function displayMessage(): string
    {
        $stored = trim((string) ($this->message ?? ''));

        $entity = $this->linkedEntity();

        if ($entity instanceof Withdrawal && $this->event_type === 'created') {
            $amount = $entity->formattedAmount();

            return match ($entity->status) {
                Withdrawal::STATUS_REJECTED => __('coin.payment_log.message_withdrawal_created_rejected', [
                    'amount' => $amount,
                ]),
                Withdrawal::STATUS_PAID => __('coin.payment_log.message_withdrawal_created_paid', [
                    'amount' => $amount,
                ]),
                default => $stored !== ''
                    ? $stored
                    : __('coin.payment_log.message_withdrawal_created', ['amount' => $amount]),
            };
        }

        if ($entity instanceof Deposit && $this->event_type === 'created') {
            $amount = $entity->formattedAmount();

            return match ($entity->status) {
                Deposit::STATUS_REJECTED => __('coin.payment_log.message_deposit_created_rejected', [
                    'amount' => $amount,
                ]),
                Deposit::STATUS_CONFIRMED => __('coin.payment_log.message_deposit_created_confirmed', [
                    'amount' => $entity->formattedCreditedAmount() ?? $amount,
                ]),
                default => $stored !== ''
                    ? $stored
                    : __('coin.payment_log.message_deposit_created', ['amount' => $amount]),
            };
        }

        return $stored !== '' ? $stored : ($this->title ?? '—');
    }

    public function indexSummary(): string
    {
        $display = $this->displayMessage();

        if ($display !== '' && $display !== '—') {
            return $display;
        }

        return $this->title ?? '—';
    }

    public function gatewayStateLabel(): string
    {
        if ($this->entity_type === 'withdrawal') {
            return PaymentStatusDecoder::ccapiPayoutState($this->gateway_state, $this->gateway_result);
        }

        return $this->gateway_state ?? '—';
    }

    public function formattedCreatedAt(): string
    {
        return LocaleFormat::dateTimeLocal($this->created_at);
    }

    public function entityAdminUrl(): ?string
    {
        if ($this->deposit_id !== null) {
            return route('admin.deposits.show', $this->deposit_id);
        }

        if ($this->withdrawal_id !== null) {
            return route('admin.withdrawals.show', $this->withdrawal_id);
        }

        return null;
    }
}

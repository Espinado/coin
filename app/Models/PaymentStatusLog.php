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

    public function statusTransitionHeading(): string
    {
        if ($this->event_type === 'created') {
            return __('coin.payment_log.status_after_event_label');
        }

        return __('coin.admin.status_transition');
    }

    public function statusTransitionDisplayLabel(): string
    {
        if (! $this->hasStatusTransition()) {
            return '—';
        }

        $eventLabel = $this->transitionLabel();

        if ($this->entityStatusDiffersFromEvent()) {
            $current = $this->currentEntityStatusLabel();

            if ($current !== null) {
                return $eventLabel.' · '.__('coin.payment_log.current_entity_status', [
                    'status' => $current,
                ]);
            }
        }

        return $eventLabel;
    }

    public function entityStatusDiffersFromEvent(): bool
    {
        $entity = $this->linkedEntity();

        if ($entity === null || $this->new_status === null) {
            return false;
        }

        return $entity->status !== $this->new_status;
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

    public function indexSummary(): string
    {
        if ($this->message !== null && $this->message !== '') {
            return $this->message;
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

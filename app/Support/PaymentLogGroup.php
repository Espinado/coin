<?php

namespace App\Support;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Collection;

final class PaymentLogGroup
{
    /** @param  Collection<int, PaymentStatusLog>  $events */
    public function __construct(
        public readonly string $entityType,
        public readonly ?int $depositId,
        public readonly ?int $withdrawalId,
        public readonly ?string $reference,
        public readonly Collection $events,
    ) {}

    public static function groupKey(PaymentStatusLog $log): string
    {
        if ($log->deposit_id !== null) {
            return 'deposit:'.$log->deposit_id;
        }

        if ($log->withdrawal_id !== null) {
            return 'withdrawal:'.$log->withdrawal_id;
        }

        return 'reference:'.($log->reference ?? 'unknown');
    }

    public function key(): string
    {
        if ($this->depositId !== null) {
            return 'deposit:'.$this->depositId;
        }

        if ($this->withdrawalId !== null) {
            return 'withdrawal:'.$this->withdrawalId;
        }

        return 'reference:'.($this->reference ?? 'unknown');
    }

    public function entity(): Deposit|Withdrawal|null
    {
        $latest = $this->events->last();

        return $latest?->linkedEntity();
    }

    public function user(): ?User
    {
        $entity = $this->entity();

        if ($entity instanceof Deposit || $entity instanceof Withdrawal) {
            return $entity->user;
        }

        return $this->events->last()?->user;
    }

    public function displayReference(): string
    {
        if ($this->reference !== null && $this->reference !== '') {
            return $this->reference;
        }

        $entity = $this->entity();

        if ($entity instanceof Deposit) {
            return $entity->publicReference();
        }

        if ($entity instanceof Withdrawal) {
            return $entity->reference ?? '—';
        }

        return '—';
    }

    public function entityLabel(): string
    {
        return PaymentStatusDecoder::entityTypeLabel($this->entityType);
    }

    public function currentStatusLabel(): string
    {
        $latest = $this->events->last();

        return $latest?->entityStatusDisplayLabel() ?? '—';
    }

    public function lastEventAt(): string
    {
        $latest = $this->events->last();

        return $latest?->formattedCreatedAt() ?? '—';
    }

    public function summaryMessage(): string
    {
        $latest = $this->events->last();

        if ($latest === null) {
            return '—';
        }

        return $latest->indexSummary();
    }

    public function entityAdminUrl(): ?string
    {
        if ($this->depositId !== null) {
            return route('admin.deposits.show', $this->depositId);
        }

        if ($this->withdrawalId !== null) {
            return route('admin.withdrawals.show', $this->withdrawalId);
        }

        return $this->events->last()?->entityAdminUrl();
    }

    public function eventsCount(): int
    {
        return $this->events->count();
    }

    public function latestResultLabel(): string
    {
        $latest = $this->events->last();

        return $latest?->resultLabel() ?? '—';
    }

    /** @return list<string> */
    public function sourceLabels(): array
    {
        return $this->events
            ->map(fn (PaymentStatusLog $log) => $log->sourceLabel())
            ->unique()
            ->values()
            ->all();
    }
}

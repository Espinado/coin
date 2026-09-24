<?php

namespace App\Support;

use App\Models\Deposit;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Carbon\CarbonInterface;

readonly class WalletHistoryEntry
{
    public function __construct(
        public string $kind,
        public ?int $entityId,
        public CarbonInterface $occurredAt,
        public int $sortOrder,
        public string $typeLabel,
        public string $sourceLabel,
        public ?string $detail,
        public string $statusLabel,
        public string $statusColor,
        public string $amountLabel,
        public string $amountColor,
        public ?string $detailColor = null,
        public ?string $rowBackground = null,
        public ?int $depositId = null,
        public bool $canReopenPaymentDetails = false,
        public float $amountNumeric = 0,
        public string $searchBlob = '',
    ) {}

    public static function fromTransaction(WalletTransaction $transaction): self
    {
        return new self(
            kind: 'transaction',
            entityId: $transaction->id,
            occurredAt: $transaction->occurred_at ?? now(),
            sortOrder: (int) $transaction->sort_order,
            typeLabel: $transaction->displayType(),
            sourceLabel: $transaction->displaySource(),
            detail: null,
            statusLabel: $transaction->displayStatus(),
            statusColor: $transaction->statusColor(),
            amountLabel: (string) $transaction->amount_label,
            amountColor: $transaction->amountColor(),
            amountNumeric: (float) $transaction->amount,
            searchBlob: implode(' ', array_filter([
                $transaction->type,
                $transaction->displayType(),
                $transaction->source,
                $transaction->displaySource(),
                $transaction->amount_label,
                $transaction->status_label,
                $transaction->displayStatus(),
                $transaction->occurred_label,
            ])),
        );
    }

    public static function fromDeposit(Deposit $deposit): self
    {
        $background = match ($deposit->status) {
            Deposit::STATUS_PENDING => 'rgba(255,180,84,0.06)',
            Deposit::STATUS_REJECTED => 'rgba(255,120,120,0.05)',
            default => null,
        };

        return new self(
            kind: 'deposit',
            entityId: $deposit->id,
            occurredAt: $deposit->created_at ?? now(),
            sortOrder: $deposit->id,
            typeLabel: __('coin.wallet.pending_top_up_type'),
            sourceLabel: $deposit->publicReference(),
            detail: $deposit->userStatusDetail(),
            detailColor: $deposit->status === Deposit::STATUS_REJECTED
                ? '#ffb0b0'
                : 'rgba(214,238,248,0.72)',
            statusLabel: $deposit->userStatusLabel(),
            statusColor: $deposit->userStatusColor(),
            amountLabel: $deposit->formattedAmount(),
            amountColor: 'rgba(214,238,248,0.88)',
            rowBackground: $background,
            depositId: $deposit->id,
            canReopenPaymentDetails: $deposit->canReopenPaymentDetails(),
            amountNumeric: (float) $deposit->amount,
            searchBlob: implode(' ', array_filter([
                $deposit->publicReference(),
                $deposit->formattedAmount(),
                $deposit->userStatusLabel(),
                $deposit->userStatusDetail(),
                $deposit->status,
                __('coin.wallet.pending_top_up_type'),
            ])),
        );
    }

    public static function fromWithdrawal(Withdrawal $withdrawal): self
    {
        $isRejected = $withdrawal->status === Withdrawal::STATUS_REJECTED;
        $isPending = in_array($withdrawal->status, Withdrawal::openStatuses(), true);

        $statusLabel = $isRejected
            ? (__('coin.withdrawal_status.rejected'))
            : __('coin.wallet.pending_status');

        $statusColor = $isRejected
            ? '#ff8f8f'
            : 'oklch(0.88 0.15 90)';

        $detail = $isRejected ? $withdrawal->userRejectionMessage() : null;

        return new self(
            kind: 'withdrawal',
            entityId: $withdrawal->id,
            occurredAt: $withdrawal->created_at ?? now(),
            sortOrder: 1_000_000 + $withdrawal->id,
            typeLabel: __('coin.wallet.pending_payout_type'),
            sourceLabel: (string) $withdrawal->reference,
            detail: $detail,
            detailColor: $isRejected ? '#ffb0b0' : null,
            statusLabel: $statusLabel,
            statusColor: $statusColor,
            amountLabel: '−'.$withdrawal->formattedAmount(),
            amountColor: $isRejected ? 'rgba(214,238,248,0.72)' : 'rgba(255,210,150,0.88)',
            rowBackground: $isPending ? 'rgba(255,180,84,0.06)' : ($isRejected ? 'rgba(255,120,120,0.05)' : null),
            amountNumeric: -abs((float) $withdrawal->amount),
            searchBlob: implode(' ', array_filter([
                $withdrawal->reference,
                $withdrawal->formattedAmount(),
                $statusLabel,
                $detail,
                $withdrawal->status,
                __('coin.wallet.pending_payout_type'),
            ])),
        );
    }
}

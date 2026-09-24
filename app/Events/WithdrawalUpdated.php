<?php

namespace App\Events;

use App\Models\Withdrawal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Withdrawal $withdrawal,
    ) {}

    /** @return array<int, \Illuminate\Broadcasting\PrivateChannel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.withdrawals'),
        ];

        if ($this->withdrawal->user_id) {
            $channels[] = new PrivateChannel('wallet.user.'.$this->withdrawal->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'WithdrawalUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->withdrawal->loadMissing('user.wallet');
        $wallet = $this->withdrawal->user?->wallet;

        return [
            'withdrawal' => [
                'id' => $this->withdrawal->id,
                'reference' => $this->withdrawal->reference,
                'status' => $this->withdrawal->status,
                'status_label' => $this->withdrawal->statusLabel(),
                'amount' => $this->withdrawal->formattedAmount(),
                'rejection_message' => $this->withdrawal->userRejectionMessage(),
            ],
            'pending_withdrawals_count' => Withdrawal::pendingCountForAdmin(),
            'wallet' => $wallet ? [
                'balance' => $wallet->formattedBalance(),
                'available' => $wallet->formattedAvailable(),
                'pending' => $wallet->formattedPending(),
                'locked' => $wallet->formattedLocked(),
            ] : null,
        ];
    }
}

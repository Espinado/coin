<?php

namespace App\Events;

use App\Models\Deposit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Deposit $deposit,
    ) {}

    /** @return array<int, \Illuminate\Broadcasting\PrivateChannel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.deposits'),
        ];

        if ($this->deposit->user_id) {
            $channels[] = new PrivateChannel('wallet.user.'.$this->deposit->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'DepositUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->deposit->loadMissing('user.wallet');
        $wallet = $this->deposit->user?->wallet;

        return [
            'deposit' => [
                'id' => $this->deposit->id,
                'reference' => 'TOP-'.$this->deposit->id,
                'status' => $this->deposit->status,
                'amount' => $this->deposit->formattedAmount(),
                'rejection_message' => $this->deposit->userRejectionMessage(),
            ],
            'wallet' => $wallet ? [
                'balance' => $wallet->formattedBalance(),
                'available' => $wallet->formattedAvailable(),
                'pending' => $wallet->formattedPending(),
                'locked' => $wallet->formattedLocked(),
            ] : null,
        ];
    }
}

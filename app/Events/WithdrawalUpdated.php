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
        return [
            new PrivateChannel('admin.withdrawals'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'WithdrawalUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'withdrawal' => [
                'id' => $this->withdrawal->id,
                'reference' => $this->withdrawal->reference,
                'status' => $this->withdrawal->status,
                'status_label' => $this->withdrawal->statusLabel(),
            ],
            'pending_withdrawals_count' => Withdrawal::pendingCountForAdmin(),
        ];
    }
}

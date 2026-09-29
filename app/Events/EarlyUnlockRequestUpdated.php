<?php

namespace App\Events;

use App\Models\EarlyUnlockRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EarlyUnlockRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EarlyUnlockRequest $request,
    ) {}

    /** @return array<int, \Illuminate\Broadcasting\PrivateChannel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.early-unlocks'),
        ];

        if ($this->request->user_id) {
            $channels[] = new PrivateChannel('wallet.user.'.$this->request->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'EarlyUnlockRequestUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->request->loadMissing(['user.wallet', 'contract.plan']);
        $wallet = $this->request->user?->wallet;

        return [
            'request' => [
                'id' => $this->request->id,
                'reference' => $this->request->reference,
                'status' => $this->request->status,
                'status_label' => $this->request->statusLabel(),
                'contract_id' => $this->request->contract_id,
                'contract_code' => $this->request->contract?->code,
                'credit' => $this->request->formattedCredit(),
                'fee' => $this->request->formattedFee(),
                'user_name' => $this->request->user?->name,
                'user_email' => $this->request->user?->email,
            ],
            'wallet' => $wallet ? [
                'balance' => $wallet->formattedBalance(),
                'available' => $wallet->formattedAvailable(),
                'pending' => $wallet->formattedPending(),
                'locked' => $wallet->formattedLocked(),
            ] : null,
            'pending_early_unlocks_count' => EarlyUnlockRequest::pendingCountForAdmin(),
            'toast' => $this->toastMessage(),
            'user_toast' => $this->userToastMessage(),
        ];
    }

    private function userToastMessage(): ?string
    {
        return match ($this->request->status) {
            EarlyUnlockRequest::STATUS_APPROVED => __('coin.messages.early_unlock_approved', [
                'credit' => $this->request->formattedCredit(),
            ]),
            EarlyUnlockRequest::STATUS_REJECTED => __('coin.messages.early_unlock_rejected'),
            default => null,
        };
    }

    private function toastMessage(): ?string
    {
        $userName = $this->request->user?->name ?? __('coin.user');

        return match ($this->request->status) {
            EarlyUnlockRequest::STATUS_PENDING => __('coin.admin.early_unlock_toast_new', ['user' => $userName]),
            EarlyUnlockRequest::STATUS_APPROVED => __('coin.admin.early_unlock_toast_approved', ['user' => $userName]),
            EarlyUnlockRequest::STATUS_REJECTED => __('coin.admin.early_unlock_toast_rejected', ['user' => $userName]),
            default => null,
        };
    }
}

<?php

namespace App\Events;

use App\Models\PlanChangeRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlanChangeRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PlanChangeRequest $request,
    ) {}

    /** @return array<int, \Illuminate\Broadcasting\PrivateChannel> */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.plan-changes'),
        ];

        if ($this->request->user_id) {
            $channels[] = new PrivateChannel('wallet.user.'.$this->request->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'PlanChangeRequestUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->request->loadMissing(['user', 'contract.plan', 'fromPlan', 'toPlan']);

        return [
            'request' => [
                'id' => $this->request->id,
                'reference' => $this->request->reference,
                'status' => $this->request->status,
                'status_label' => $this->request->statusLabel(),
                'contract_id' => $this->request->contract_id,
                'contract_code' => $this->request->contract?->code,
                'from_plan' => $this->request->fromPlan?->displayName(),
                'to_plan' => $this->request->toPlan?->displayName(),
                'top_up' => $this->request->formattedTopUp(),
                'user_name' => $this->request->user?->name,
                'user_email' => $this->request->user?->email,
            ],
            'pending_plan_changes_count' => PlanChangeRequest::pendingCountForAdmin(),
            'toast' => $this->toastMessage(),
            'user_toast' => $this->userToastMessage(),
        ];
    }

    private function userToastMessage(): ?string
    {
        return match ($this->request->status) {
            PlanChangeRequest::STATUS_APPROVED => __('coin.messages.plan_change_confirmed'),
            PlanChangeRequest::STATUS_REJECTED => __('coin.messages.plan_change_rejected'),
            default => null,
        };
    }

    private function toastMessage(): ?string
    {
        $userName = $this->request->user?->name ?? __('coin.user');

        return match ($this->request->status) {
            PlanChangeRequest::STATUS_PENDING => __('coin.admin.plan_change_toast_new', ['user' => $userName]),
            PlanChangeRequest::STATUS_APPROVED => __('coin.admin.plan_change_toast_approved', ['user' => $userName]),
            PlanChangeRequest::STATUS_REJECTED => __('coin.admin.plan_change_toast_rejected', ['user' => $userName]),
            default => null,
        };
    }
}

<?php

namespace App\Events;

use App\Models\ReferralCommission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReferralCommissionPaid implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ReferralCommission $commission,
    ) {}

    /** @return array<int, \Illuminate\Broadcasting\PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('wallet.user.'.$this->commission->referrer_user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ReferralCommissionPaid';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->commission->loadMissing(['referrer.wallet', 'referral', 'contract.plan']);
        $wallet = $this->commission->referrer?->wallet;
        $currency = $this->commission->currency ?? 'USDT';
        $amount = (float) $this->commission->commission_amount;

        return [
            'commission' => [
                'id' => $this->commission->id,
                'amount' => number_format($amount, 2, '.', ','),
                'currency' => $currency,
                'referral_label' => $this->commission->referralLabel(),
                'plan_name' => $this->commission->planName(),
            ],
            'user_toast' => __('coin.messages.referral_commission_received', [
                'amount' => number_format($amount, 2, '.', ','),
                'currency' => $currency,
                'user' => $this->commission->referralLabel(),
            ]),
            'wallet' => $wallet ? [
                'balance' => $wallet->formattedBalance(),
                'available' => $wallet->formattedAvailable(),
                'pending' => $wallet->formattedPending(),
                'locked' => $wallet->formattedLocked(),
            ] : null,
        ];
    }
}

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
    public const SOURCE_PURCHASE = 'purchase';

    public const SOURCE_UPGRADE = 'upgrade';

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ReferralCommission $commission,
        public string $source,
        public float $payoutAmount,
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
        $amountLabel = number_format($this->payoutAmount, 2, '.', ',');
        $referralLabel = $this->commission->referralLabel();

        $toastKey = $this->source === self::SOURCE_UPGRADE
            ? 'coin.messages.referral_commission_received_upgrade'
            : 'coin.messages.referral_commission_received_purchase';

        return [
            'commission' => [
                'id' => $this->commission->id,
                'source' => $this->source,
                'amount' => $amountLabel,
                'currency' => $currency,
                'referral_label' => $referralLabel,
                'plan_name' => $this->commission->planName(),
            ],
            'user_toast' => __($toastKey, [
                'amount' => $amountLabel,
                'currency' => $currency,
                'user' => $referralLabel,
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

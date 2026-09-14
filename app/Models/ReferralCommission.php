<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    protected $fillable = [
        'referrer_user_id',
        'referral_user_id',
        'contract_id',
        'purchase_amount',
        'commission_percent',
        'commission_amount',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'purchase_amount' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_user_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function referralLabel(): string
    {
        return $this->referral?->accountLabel() ?? 'Referral';
    }

    public function planName(): string
    {
        return $this->contract?->plan?->name ?? '—';
    }

    public function formattedCommission(): string
    {
        return '+'.number_format((float) $this->commission_amount, 2, '.', ',').' '.$this->currency;
    }

    public function occurredLabel(): string
    {
        return $this->created_at?->format('M j · H:i') ?? '—';
    }
}

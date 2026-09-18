<?php

namespace App\Models;

use App\Support\MoneyFormat;
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

    public function scopeSearchTerm($query, string $term)
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function ($inner) use ($like) {
            $inner->where('currency', 'like', $like)
                ->orWhere('purchase_amount', 'like', $like)
                ->orWhere('commission_amount', 'like', $like)
                ->orWhereHas('referral', function ($userQuery) use ($like) {
                    $userQuery->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('account_slug', 'like', $like);
                })
                ->orWhereHas('contract.plan', function ($planQuery) use ($like) {
                    $planQuery->where('name', 'like', $like);
                });
        });
    }

    public function scopeApplyListSort($query, string $sort, string $dir, string $defaultColumn = 'created_at')
    {
        $direction = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        $allowed = [
            'created_at' => 'created_at',
            'user' => 'referral_user_id',
            'plan' => 'contract_id',
            'purchase' => 'purchase_amount',
            'commission' => 'commission_amount',
        ];

        if ($sort === 'plan') {
            $query->leftJoin('contracts', 'contracts.id', '=', 'referral_commissions.contract_id')
                ->leftJoin('plans', 'plans.id', '=', 'contracts.plan_id')
                ->orderBy('plans.name', $direction)
                ->select('referral_commissions.*');
        } elseif ($sort === 'user') {
            $query->leftJoin('users', 'users.id', '=', 'referral_commissions.referral_user_id')
                ->orderBy('users.name', $direction)
                ->select('referral_commissions.*');
        } elseif ($sort !== '' && array_key_exists($sort, $allowed)) {
            $query->orderBy($allowed[$sort], $direction);
        } else {
            $query->orderBy($defaultColumn, 'desc');
        }

        return $query->orderByDesc('referral_commissions.id');
    }

    public function referralLabel(): string
    {
        return $this->referral?->accountLabel() ?? 'Referral';
    }

    public function planName(): string
    {
        return $this->contract?->plan?->name ?? '—';
    }

    public function formattedPurchaseAmount(): string
    {
        return MoneyFormat::amount($this->purchase_amount, $this->currency, 0);
    }

    public function formattedCommission(): string
    {
        return MoneyFormat::signedAmount($this->commission_amount, $this->currency);
    }

    public function occurredLabel(): string
    {
        return $this->created_at?->format('M j · H:i') ?? '—';
    }
}

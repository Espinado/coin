<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const KYC_NONE = 'none';

    public const KYC_PENDING = 'pending';

    public const KYC_APPROVED = 'approved';

    public const KYC_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'email',
        'password',
        'account_slug',
        'epoch_label',
        'active_tflops',
        'nodes_label',
        'expected_daily_reward',
        'is_blocked',
        'kyc_status',
        'avg_epoch_label',
        'availability_label',
        'load_label',
        'next_expiry_label',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expected_daily_reward' => 'decimal:2',
            'is_blocked' => 'boolean',
        ];
    }

    /** @return array<string, string> */
    public static function kycStatuses(): array
    {
        return [
            self::KYC_NONE => 'None',
            self::KYC_PENDING => 'Pending',
            self::KYC_APPROVED => 'Approved',
            self::KYC_REJECTED => 'Rejected',
        ];
    }

    public function kycLabel(): string
    {
        return self::kycStatuses()[$this->kyc_status] ?? ucfirst($this->kyc_status);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function rewardPeriodTotals(): HasMany
    {
        return $this->hasMany(RewardPeriodTotal::class);
    }

    public function referralProfile(): HasOne
    {
        return $this->hasOne(ReferralProfile::class);
    }

    public function referralAccruals(): HasMany
    {
        return $this->hasMany(ReferralAccrual::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function accountLabel(): string
    {
        return 'Account '.($this->account_slug ?? substr(md5((string) $this->id), 0, 4));
    }

    public function formattedDailyReward(): string
    {
        $value = (float) $this->expected_daily_reward;

        return ($value >= 0 ? '+' : '').number_format($value, 2, '.', '');
    }
}

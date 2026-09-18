<?php

namespace App\Models;

use App\Services\EmailVerificationCodeService;
use App\Support\MoneyFormat;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'phone',
        'telegram',
        'country_code',
        'last_login_at',
        'admin_lead_note',
        'password',
        'referred_by_user_id',
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
        'email_two_factor_enabled',
        'notify_profit_credit',
        'notify_contract_expiry',
        'notify_maturity_alerts',
        'notify_referral_activity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'expected_daily_reward' => 'decimal:2',
            'is_blocked' => 'boolean',
            'email_two_factor_enabled' => 'boolean',
            'notify_profit_credit' => 'boolean',
            'notify_contract_expiry' => 'boolean',
            'notify_maturity_alerts' => 'boolean',
            'notify_referral_activity' => 'boolean',
        ];
    }

    public function hasEmailTwoFactorEnabled(): bool
    {
        return (bool) $this->email_two_factor_enabled;
    }

    public function sendEmailVerificationNotification(): void
    {
        app(EmailVerificationCodeService::class)->sendCode($this);
    }

    public function wantsNotification(string $type): bool
    {
        return match ($type) {
            'profit_credit' => (bool) $this->notify_profit_credit,
            'contract_expiry' => (bool) $this->notify_contract_expiry,
            'maturity_alert' => (bool) $this->notify_maturity_alerts,
            'referral_activity' => (bool) $this->notify_referral_activity,
            'payout_completed' => true,
            'plan_change_approved' => true,
            default => false,
        };
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

    public function kycBadgeStyle(): string
    {
        return match ($this->kyc_status) {
            self::KYC_APPROVED => 'background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); color: oklch(0.88 0.14 160);',
            self::KYC_REJECTED => 'background: rgba(255,143,143,0.15); border: 1px solid rgba(255,143,143,0.35); color: #ff8f8f;',
            self::KYC_PENDING => 'background: oklch(0.7 0.15 90 / 0.16); border: 1px solid oklch(0.8 0.14 90 / 0.4); color: oklch(0.9 0.14 90);',
            default => 'background: rgba(150,235,250,0.08); border: 1px solid rgba(150,235,250,0.2); color: rgba(214,238,248,0.75);',
        };
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

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_user_id');
    }

    public function directReferrals(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_user_id');
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

    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function referralCommissionsEarned(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_user_id');
    }

    public function accountLabel(): string
    {
        if (filled($this->name)) {
            return (string) $this->name;
        }

        return __('coin.profile.account_fallback', [
            'id' => $this->account_slug ?? substr(md5((string) $this->id), 0, 4),
        ]);
    }

    public function avatarInitial(): string
    {
        if (filled($this->name)) {
            return mb_strtoupper(mb_substr(trim($this->name), 0, 1));
        }

        if (filled($this->email)) {
            return mb_strtoupper(mb_substr($this->email, 0, 1));
        }

        return 'C';
    }

    public function formattedDailyReward(): string
    {
        $currency = $this->wallet?->currencyCode() ?? MoneyFormat::currency();

        return MoneyFormat::signedAmount($this->expected_daily_reward, $currency);
    }
}

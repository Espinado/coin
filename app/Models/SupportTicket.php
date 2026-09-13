<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CLOSED = 'closed';

    public const CATEGORY_WITHDRAWAL = 'withdrawal';

    public const CATEGORY_CONTRACT = 'contract';

    public const CATEGORY_KYC = 'kyc';

    public const CATEGORY_ACCOUNT = 'account';

    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'assigned_admin_id',
        'reference',
        'subject',
        'category',
        'status',
        'last_reply_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
        ];
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_WITHDRAWAL => 'Withdrawal',
            self::CATEGORY_CONTRACT => 'Contract',
            self::CATEGORY_KYC => 'KYC',
            self::CATEGORY_ACCOUNT => 'Account',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)->orderBy('created_at');
    }

    public function categoryLabel(): string
    {
        return self::categories()[$this->category] ?? ucfirst($this->category);
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'oklch(0.88 0.12 192)',
            self::STATUS_PENDING => 'oklch(0.9 0.14 90)',
            self::STATUS_CLOSED => 'rgba(214,238,248,0.65)',
            default => 'rgba(214,238,248,0.78)',
        };
    }
}

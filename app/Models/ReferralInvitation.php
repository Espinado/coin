<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralInvitation extends Model
{
    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_LINK = 'link';

    protected $fillable = [
        'referrer_user_id',
        'email',
        'channel',
        'sent_at',
        'registered_user_id',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'registered_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function registeredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    public function scopeForReferrer($query, int $referrerUserId)
    {
        return $query->where('referrer_user_id', $referrerUserId);
    }

    public function scopeSearchTerm($query, string $term)
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function ($inner) use ($like, $term) {
            $inner->where('email', 'like', $like)
                ->orWhere('channel', 'like', $like)
                ->orWhereHas('registeredUser', function ($userQuery) use ($like) {
                    $userQuery->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('account_slug', 'like', $like);
                });

            foreach (['registered', 'pending'] as $statusKey) {
                if (str_contains(mb_strtolower(__('coin.referrals.status_'.$statusKey)), mb_strtolower($term))) {
                    if ($statusKey === 'registered') {
                        $inner->orWhereNotNull('registered_user_id');
                    } else {
                        $inner->orWhereNull('registered_user_id');
                    }
                }
            }

            foreach ([self::CHANNEL_EMAIL, self::CHANNEL_LINK] as $channel) {
                if (str_contains(mb_strtolower(__('coin.referrals.channel_'.$channel)), mb_strtolower($term))) {
                    $inner->orWhere('channel', $channel);
                }
            }
        });
    }

    public function scopeApplyListSort($query, string $sort, string $dir, string $defaultColumn = 'sent_at')
    {
        $direction = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        $allowed = [
            'email' => 'email',
            'channel' => 'channel',
            'sent_at' => 'sent_at',
            'registered_at' => 'registered_at',
            'status' => 'registered_at',
        ];

        if ($sort !== '' && array_key_exists($sort, $allowed)) {
            $query->orderBy($allowed[$sort], $direction);
        } else {
            $query->orderBy($defaultColumn, 'desc');
        }

        return $query->orderByDesc('id');
    }

    public function isRegistered(): bool
    {
        return $this->registered_user_id !== null;
    }

    public function statusKey(): string
    {
        return $this->isRegistered() ? 'registered' : 'pending';
    }

    public function statusLabel(): string
    {
        return __('coin.referrals.status_'.$this->statusKey());
    }

    public function channelLabel(): string
    {
        return __('coin.referrals.channel_'.$this->channel);
    }

    public function displayLabel(): string
    {
        if ($this->registeredUser) {
            return $this->registeredUser->accountLabel();
        }

        return $this->email;
    }

    public function formattedSentAt(): string
    {
        return $this->sent_at?->format('d.m.Y H:i') ?? '—';
    }

    public function formattedRegisteredAt(): string
    {
        return $this->registered_at?->format('d.m.Y H:i') ?? '—';
    }
}

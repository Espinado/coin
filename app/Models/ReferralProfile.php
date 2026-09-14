<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralProfile extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'invited_count',
        'active_contracts',
        'total_rewards',
        'level1_percent',
        'level2_percent',
        'level1_users',
        'level2_users',
    ];

    protected function casts(): array
    {
        return [
            'total_rewards' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedTotalRewards(): string
    {
        return '+'.number_format((float) $this->total_rewards, 2, '.', ',');
    }

    public function formattedRewardsBalance(): string
    {
        return number_format((float) $this->total_rewards, 2, '.', ',');
    }

    public function level1BarPercent(): int
    {
        $total = max(1, $this->level1_users + $this->level2_users);

        return (int) round(($this->level1_users / $total) * 100);
    }

    public function level2BarPercent(): int
    {
        $total = max(1, $this->level1_users + $this->level2_users);

        return (int) round(($this->level2_users / $total) * 100);
    }

    public function commissionLabel(): string
    {
        return "{$this->level1_percent}% on plan purchase";
    }

    public function shareUrl(): string
    {
        $domain = config('coin.user_domain');
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return "{$scheme}://{$domain}/r/{$this->code}";
    }

    public function sharePath(): string
    {
        return '/r/'.$this->code;
    }
}

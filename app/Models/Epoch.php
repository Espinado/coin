<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Epoch extends Model
{
    protected $fillable = [
        'number',
        'reward_rate',
        'epochs_per_day',
        'contracts_settled',
        'total_rewards',
        'triggered_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_rate' => 'decimal:6',
            'total_rewards' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function triggeredByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'triggered_by');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(EpochReward::class);
    }

    public function formattedTotalRewards(): string
    {
        return number_format((float) $this->total_rewards, 2, '.', ',');
    }
}

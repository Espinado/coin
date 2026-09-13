<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'occurred_label',
        'type',
        'source',
        'amount_label',
        'amount_tone',
        'status_label',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function amountColor(): string
    {
        return match ($this->amount_tone) {
            'positive' => 'oklch(0.88 0.12 192)',
            'warning' => 'oklch(0.88 0.15 90)',
            default => 'rgba(214,238,248,0.8)',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status_label) {
            'PENDING' => 'oklch(0.88 0.15 90)',
            default => 'oklch(0.88 0.14 160)',
        };
    }
}

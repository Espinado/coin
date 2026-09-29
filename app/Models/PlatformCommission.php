<?php

namespace App\Models;

use App\Support\MoneyFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlatformCommission extends Model
{
    public const KIND_WITHDRAWAL = 'withdrawal';

    public const KIND_EARLY_UNLOCK = 'early_unlock';

    protected $fillable = [
        'user_id',
        'kind',
        'reference',
        'amount',
        'currency',
        'source_type',
        'source_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function kindLabel(): string
    {
        $key = 'coin.admin.commission_kind.'.$this->kind;

        return __($key) !== $key ? __($key) : ucfirst(str_replace('_', ' ', (string) $this->kind));
    }

    public function formattedAmount(): string
    {
        return MoneyFormat::amount($this->amount, $this->currency);
    }
}

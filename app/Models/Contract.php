<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'code',
        'status',
        'tflops',
        'duration_days',
        'days_elapsed',
        'accrued_amount',
        'progress_percent',
        'started_label',
        'ends_label',
        'location_label',
        'completed_summary',
    ];

    protected function casts(): array
    {
        return [
            'accrued_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function formattedAccrued(): string
    {
        return number_format((float) $this->accrued_amount, 2, '.', ',');
    }

    public function formattedTflops(): string
    {
        return number_format($this->tflops, 0, '.', ',');
    }

    public function statusLabel(): string
    {
        return strtoupper($this->status);
    }

    public function title(): string
    {
        return 'Contract '.($this->plan?->name ?? '—');
    }
}

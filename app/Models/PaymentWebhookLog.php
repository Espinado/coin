<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhookLog extends Model
{
    public const RESULT_PROCESSED = 'processed';

    public const RESULT_IGNORED = 'ignored';

    public const RESULT_DUPLICATE = 'duplicate';

    public const RESULT_FAILED = 'failed';

    protected $fillable = [
        'gateway',
        'event_type',
        'payload',
        'signature_valid',
        'idempotency_key',
        'deposit_id',
        'withdrawal_id',
        'processing_result',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }
}

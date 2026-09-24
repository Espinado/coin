<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    public static function formatProcessingResult(string $result, string $message, int $maxLength = 1000): string
    {
        $text = $result.': '.$message;

        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - 3).'...';
    }

    public function resultType(): ?string
    {
        [$result] = \App\Support\PaymentStatusDecoder::splitProcessingResult($this->processing_result);

        return $result;
    }

    public function isDuplicateResult(): bool
    {
        return $this->resultType() === self::RESULT_DUPLICATE;
    }

    public function scopeExcludeDuplicateResults($query)
    {
        return $query->where(function ($inner) {
            $inner->whereNull('processing_result')
                ->orWhere('processing_result', 'not like', self::RESULT_DUPLICATE.':%');
        });
    }

    public function scopeLinkedToDeposit($query, Deposit $deposit)
    {
        $gatewayLabel = $deposit->gateway_uniq_id ?? Deposit::gatewayUniqId($deposit->id);

        return $query->where(function ($inner) use ($deposit, $gatewayLabel) {
            $inner->where('deposit_id', $deposit->id);

            $inner->orWhere(function ($payloadQuery) use ($deposit, $gatewayLabel) {
                $payloadQuery->where('payload->label', $gatewayLabel);

                if ($deposit->txid) {
                    $payloadQuery->orWhere('payload->txid', $deposit->txid);
                }
            });
        });
    }

    /** @return EloquentCollection<int, self> */
    public static function journalForDeposit(Deposit $deposit, int $limit = 20): EloquentCollection
    {
        $query = static::query()
            ->linkedToDeposit($deposit)
            ->orderByDesc('id');

        $primary = (clone $query)
            ->excludeDuplicateResults()
            ->limit($limit)
            ->get();

        if ($primary->isNotEmpty()) {
            return $primary;
        }

        return $query->limit($limit)->get();
    }
}

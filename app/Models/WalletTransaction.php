<?php

namespace App\Models;

use App\Support\LocaleFormat;
use App\Support\PlatformTerms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    /** @return list<string> */
    public static function profitHistoryTypes(): array
    {
        return ['Daily profit', 'Referral credit', 'Principal release'];
    }

    public function scopeProfitHistory($query)
    {
        return $query->whereIn('type', self::profitHistoryTypes());
    }

    public function scopeSearchTerm($query, string $term)
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';
        $matchingTypes = self::matchingTypesForSearch($term);
        $matchingStatuses = self::matchingStatusesForSearch($term);

        return $query->where(function ($inner) use ($like, $matchingTypes, $matchingStatuses) {
            $inner->where('source', 'like', $like)
                ->orWhere('amount_label', 'like', $like)
                ->orWhere('occurred_label', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('status_label', 'like', $like);

            if ($matchingTypes !== []) {
                $inner->orWhereIn('type', $matchingTypes);
            }

            if ($matchingStatuses !== []) {
                $inner->orWhereIn('status_label', $matchingStatuses);
            }
        });
    }

    public function scopeApplyListSort($query, string $sort, string $dir, string $defaultColumn = 'occurred_at')
    {
        $direction = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        $allowed = [
            'occurred_at' => 'occurred_at',
            'type' => 'type',
            'source' => 'source',
            'status' => 'status_label',
            'amount' => 'amount',
        ];

        if ($sort !== '' && array_key_exists($sort, $allowed)) {
            $query->orderBy($allowed[$sort], $direction);
        } else {
            $query->orderBy($defaultColumn, 'desc');
        }

        return $query->orderByDesc('id');
    }

    /** @return list<string> */
    private static function matchingTypesForSearch(string $term): array
    {
        return self::matchingLabelsForSearch($term, 'coin.tx', array_keys(__('coin.tx')));
    }

    /** @return list<string> */
    private static function matchingStatusesForSearch(string $term): array
    {
        return self::matchingLabelsForSearch($term, 'coin.tx_status', array_keys(__('coin.tx_status')));
    }

    /** @param  list<string>  $keys
     * @return list<string>
     */
    private static function matchingLabelsForSearch(string $term, string $prefix, array $keys): array
    {
        $needle = mb_strtolower(trim($term));

        if ($needle === '') {
            return [];
        }

        $matches = [];

        foreach ($keys as $key) {
            $label = __($prefix.'.'.$key);
            $haystacks = [mb_strtolower((string) $key), mb_strtolower($label)];

            foreach ($haystacks as $haystack) {
                if (str_contains($haystack, $needle)) {
                    $matches[] = $key;
                    break;
                }
            }
        }

        return array_values(array_unique($matches));
    }

    protected $fillable = [
        'user_id',
        'occurred_label',
        'type',
        'source',
        'amount_label',
        'amount_tone',
        'status_label',
        'sort_order',
        'amount',
        'currency',
        'reference_type',
        'reference_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayType(): string
    {
        return PlatformTerms::displayTransactionType($this->type);
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

    public function formattedOccurredAt(): string
    {
        if ($this->occurred_at) {
            return LocaleFormat::shortDateTime($this->occurred_at);
        }

        return (string) $this->occurred_label;
    }

    public function displayStatus(): string
    {
        return PlatformTerms::displayTransactionStatus((string) $this->status_label);
    }

    public function displaySource(): string
    {
        return PlatformTerms::displayTransactionSource((string) $this->source);
    }
}

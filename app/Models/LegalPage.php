<?php

namespace App\Models;

use App\Support\LegalFaq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    public const SLUG_TERMS = 'terms';

    public const SLUG_PRIVACY = 'privacy';

    public const SLUG_RISKS = 'risks';

    public const SLUG_FAQ = 'faq';

    protected $fillable = [
        'slug',
        'title',
        'body',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return [
            self::SLUG_TERMS,
            self::SLUG_PRIVACY,
            self::SLUG_RISKS,
            self::SLUG_FAQ,
        ];
    }

    public function slugLabel(): string
    {
        $key = 'coin.legal.slugs.'.$this->slug;
        $label = __($key);

        return $label === $key ? $this->slug : $label;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isFaq(): bool
    {
        return $this->slug === self::SLUG_FAQ;
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function decodedFaqItems(): array
    {
        if (! $this->isFaq()) {
            return [];
        }

        return LegalFaq::decode($this->body);
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function faqItems(): array
    {
        $items = $this->decodedFaqItems();

        return $items !== [] ? $items : LegalFaq::defaultItems();
    }

    /**
     * @param  list<array{question: string, answer: string}>  $items
     */
    public function setFaqItems(array $items): void
    {
        $this->body = LegalFaq::encode($items);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisitUnique extends Model
{
    protected $fillable = [
        'visit_date',
        'ip_hash',
        'hits',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'hits' => 'integer',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }
}

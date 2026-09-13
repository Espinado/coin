<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralAccrual extends Model
{
    protected $fillable = [
        'user_id',
        'user_label',
        'level_label',
        'plan_name',
        'amount_label',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformBroadcast extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'body',
        'recipients_count',
    ];

    protected function casts(): array
    {
        return [
            'recipients_count' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** @return HasMany<UserNotification, $this> */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }
}

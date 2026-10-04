<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mailbox extends Model
{
    protected $fillable = [
        'email',
        'local_part',
        'password',
        'quota_mb',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'quota_mb' => 'integer',
        ];
    }

    public function hasStoredPassword(): bool
    {
        return filled($this->password);
    }
}

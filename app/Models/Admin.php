<?php

namespace App\Models;

use App\Support\AdminRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => AdminRole::class,
        ];
    }

    public function adminRole(): AdminRole
    {
        return $this->role ?? AdminRole::Operator;
    }

    public function isSuperadmin(): bool
    {
        return $this->adminRole() === AdminRole::Superadmin;
    }
}

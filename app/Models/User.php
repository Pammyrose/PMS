<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'office_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super-admin';
    }


    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRegionalOffice(): bool
    {
        return in_array($this->role, ['super-admin', 'ro-office', 'ro office'], true);
    }


    public function isUser(): bool
    {
        return in_array($this->role, ['user', 'penro', 'cenro'], true);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'super-admin', 'ro-office', 'ro office' => 'Region',
            'admin' => 'Administrator',
            'penro' => 'PENRO',
            'cenro' => 'CENRO',
            'user' => 'User',
            default => 'Unknown',
        };
    }
}

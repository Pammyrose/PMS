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
        return in_array($this->role, ['admin', 'super-admin']);
    }


    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'super-admin' => 'Super Administrator',
            'admin'       => 'Administrator',
            'user'        => 'User',
            default       => 'Unknown',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'global_role',
        'registration_source',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'phone_verified_at',
        'fan_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'phone_verified_at'  => 'datetime',
            'last_login_at'      => 'datetime',
            'is_active'          => 'boolean',
            'password'           => 'hashed',
        ];
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function accesses()
    {
        return $this->hasMany(UserAccess::class);
    }
}

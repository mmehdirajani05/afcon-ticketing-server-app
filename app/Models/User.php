<?php

namespace App\Models;

use App\Constants\AppConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        // Core auth
        'name',
        'email',
        'phone',
        'password',
        'global_role',
        'admin_role_id',
        'registration_source',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'phone_verified_at',

        // Fan ID — only the final generated ID lives here.
        // All identity/document details live in user_immigration_details table.
        'fan_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(UserAccess::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(UserOtp::class);
    }

    public function immigrationLogs(): HasMany
    {
        return $this->hasMany(ImmigrationLog::class);
    }

    /**
     * All Fan ID application submissions — includes rejected history.
     * Use latestImmigrationDetail() to get the active/current one.
     */
    public function immigrationDetails(): HasMany
    {
        return $this->hasMany(UserImmigrationDetail::class);
    }

    /**
     * The most recent Fan ID application (pending or verified).
     */
    public function latestImmigrationDetail(): HasOne
    {
        return $this->hasOne(UserImmigrationDetail::class)->latestOfMany();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function adminRole(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AdminRole::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function hasFanId(): bool
    {
        return ! empty($this->fan_id);
    }

    public function isAdmin(): bool
    {
        return in_array($this->global_role, ['admin', 'sub_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->global_role === AppConstant::ROLE_ADMIN;
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->adminRole?->hasPermission($permission) ?? false;
    }
}

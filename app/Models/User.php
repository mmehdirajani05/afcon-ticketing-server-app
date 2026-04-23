<?php

namespace App\Models;

use App\Constants\AppConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        // Core
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

        // Fan ID — generated string & application details (applied once)
        'fan_id',
        'fan_id_full_name',
        'fan_id_identity_type',
        'fan_id_identity_number',
        'fan_id_nationality',
        'fan_id_date_of_birth',
        'fan_id_status',
        'fan_id_rejection_reason',
        'fan_id_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fan_id_identity_number',   // sensitive — exclude from default serialisation
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'phone_verified_at'   => 'datetime',
            'last_login_at'       => 'datetime',
            'fan_id_verified_at'  => 'datetime',
            'fan_id_date_of_birth'=> 'date',
            'is_active'           => 'boolean',
            'password'            => 'hashed',
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

    // ── Fan ID helpers ─────────────────────────────────────────────────────────

    public function hasFanId(): bool
    {
        return $this->fan_id_status === AppConstant::FAN_ID_STATUS_VERIFIED && ! empty($this->fan_id);
    }

    public function fanIdIsPending(): bool
    {
        return $this->fan_id_status === AppConstant::FAN_ID_STATUS_PENDING;
    }

    public function fanIdIsRejected(): bool
    {
        return $this->fan_id_status === AppConstant::FAN_ID_STATUS_REJECTED;
    }
}

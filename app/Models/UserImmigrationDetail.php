<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores each Fan ID application submission made by a user.
 *
 * A user may have multiple rows if previous applications were rejected and
 * they re-applied. Only one row per user may have status = pending or verified
 * at any given time (enforced in FanIdService).
 *
 * The final generated fan_id string lives on users.fan_id — not here.
 * This table is the identity proof / audit trail.
 */
class UserImmigrationDetail extends Model
{
    protected $fillable = [
        'user_id',

        // Personal
        'full_name',
        'gender',
        'date_of_birth',
        'nationality',

        // Document
        'identity_type',
        'identity_number',
        'identity_expiry_date',

        // Lifecycle
        'status',
        'rejection_reason',
        'verified_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'         => 'date',
            'identity_expiry_date'  => 'date',
            'verified_at'           => 'datetime',
            'submitted_at'          => 'datetime',
        ];
    }

    protected $hidden = [
        'identity_number',   // sensitive — excluded from default serialisation
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}

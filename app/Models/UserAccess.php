<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccess extends Model
{
    protected $fillable = [
        'user_id',
        'scope',
        'module',
        'role_name',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
        'can_approve',
        'assigned_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'can_view'   => 'boolean',
            'can_create' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
            'can_approve'=> 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

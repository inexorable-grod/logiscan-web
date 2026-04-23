<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'force_password_change',
        'last_login',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function centerAssignment(): HasOne
    {
        return $this->hasOne(CenterUser::class)->where('is_active', true);
    }

    public function centerAssignments(): HasMany
    {
        return $this->hasMany(CenterUser::class);
    }

    public function createdRequests(): HasMany
    {
        return $this->hasMany(ClientRequest::class, 'requested_by');
    }

    public function resolvedRequests(): HasMany
    {
        return $this->hasMany(ClientRequest::class, 'resolved_by');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function hasGlobalAccess(): bool
    {
        return $this->isRole('ti_admin', 'gerente_ops');
    }
}

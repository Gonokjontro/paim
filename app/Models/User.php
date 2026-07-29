<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'organization_id',
        'workspace_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar_url',
        'preferences',
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
            'preferences' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function getAvatarAttribute(): string
    {
        if ($this->avatar_url && file_exists(public_path(ltrim($this->avatar_url, '/')))) {
            return asset($this->avatar_url);
        }
        return asset('assets/media/avatars/default-avatar.svg');
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->role === 'super_admin' || $this->role === 'admin') {
            return true;
        }

        $workspace = $this->workspace;
        if (!$workspace) {
            return false;
        }

        $matrix = $workspace->getSetting('permission_matrix');
        if ($matrix && isset($matrix[$this->role])) {
            return in_array($permissionKey, $matrix[$this->role]);
        }

        // Default fallbacks
        if ($this->role === 'manager') {
            return !str_contains($permissionKey, 'replace') && !str_contains($permissionKey, 'users.') && !str_contains($permissionKey, 'settings.') && !str_contains($permissionKey, 'permissions.');
        }

        return str_contains($permissionKey, '.view');
    }
}

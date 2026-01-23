<?php

namespace Marufsharia\Hyro\Models;

use Marufsharia\Hyro\Events\RoleAssigned;
use Marufsharia\Hyro\Events\RoleRevoked;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar',
        'phone',
        'timezone',
        'locale',
        'is_active',
        'is_suspended',
        'is_super_admin',
        'suspension_reason',
        'suspended_at',
        'unsuspended_at',
        'unsuspension_reason',
        'locked_reason',
        'locked_at',
        'unlocked_at',
        'unlock_reason',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'suspended_at' => 'datetime',
        'unsuspended_at' => 'datetime',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'is_active' => 'boolean',
        'is_suspended' => 'boolean',
        'is_super_admin' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'metadata' => 'array',
        'password' => 'hashed',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'avatar_url',
        'full_name',
        'status',
        'is_online',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = $user->generateUsername();
            }
        });

        static::created(function ($user) {
            // Assign default role if configured
            $defaultRole = config('hyro.roles.default', 'user');
            if ($defaultRole && !$user->is_super_admin) {
                $role = Role::where('slug', $defaultRole)->first();
                if ($role) {
                    $user->assignRole($role);
                }
            }
        });
    }

    /**
     * Get the user's roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps()
            ->withPivot(['created_at', 'assigned_by']);
    }

    /**
     * Get the user's directly assigned privileges.
     */
    public function privileges()
    {
        return $this->belongsToMany(Privilege::class, 'privilege_user')
            ->withTimestamps()
            ->withPivot(['created_at', 'granted_by', 'scope_id', 'scope_type']);
    }

    /**
     * Get the user's audit logs.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the user's tokens.
     */
    public function tokens()
    {
        return $this->hasMany(PersonalAccessToken::class, 'tokenable_id');
    }

    /**
     * Get the user's assigned by relationships.
     */
    public function assignedBy()
    {
        return $this->hasMany(RoleUser::class, 'assigned_by');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_suspended', false);
    }

    /**
     * Scope a query to only include suspended users.
     */
    public function scopeSuspended($query)
    {
        return $query->where('is_suspended', true);
    }

    /**
     * Scope a query to only include super admins.
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope a query to only include locked users.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_active', false)->whereNotNull('locked_at');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }

        if ($role instanceof Role) {
            return $this->roles->contains('id', $role->id);
        }

        return false;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role, $assignedBy = null): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if (!$this->hasRole($role)) {
            $this->roles()->attach($role->id, [
                'assigned_by' => $assignedBy?->id ?? auth()->id(),
                'created_at' => now(),
            ]);

            // Dispatch event
            event(new RoleAssigned($this, $role, $assignedBy ?? auth()->user(), [
                'via' => 'manual',
                'assigned_at' => now(),
            ]));
        }

        return $this;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole($role, $removedBy = null): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if ($this->hasRole($role)) {
            $this->roles()->detach($role->id);

            // Dispatch event
            event(new RoleRevoked($this, $role, $removedBy ?? auth()->user(), [
                'via' => 'manual',
                'revoked_at' => now(),
            ]));
        }

        return $this;
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roles, bool $detach = true, $syncedBy = null): self
    {
        $roleIds = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = Role::where('slug', $role)->firstOrFail();
            }
            $roleIds[] = $role->id;
        }

        // Get current roles
        $currentRoles = $this->roles->pluck('id')->toArray();
        $added = array_diff($roleIds, $currentRoles);
        $removed = $detach ? array_diff($currentRoles, $roleIds) : [];

        // Sync roles
        $this->roles()->sync($roleIds);

        // Dispatch events for added roles
        foreach ($added as $roleId) {
            $role = Role::find($roleId);
            event(new RoleAssigned($this, $role, $syncedBy ?? auth()->user(), [
                'via' => 'sync',
                'assigned_at' => now(),
            ]));
        }

        // Dispatch events for removed roles
        foreach ($removed as $roleId) {
            $role = Role::find($roleId);
            event(new RoleRevoked($this, $role, $syncedBy ?? auth()->user(), [
                'via' => 'sync',
                'revoked_at' => now(),
            ]));
        }

        return $this;
    }

    /**
     * Check if user has a specific privilege.
     */
    public function hasPrivilege($privilege, $scope = null): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (is_string($privilege)) {
            $privilege = Privilege::where('slug', $privilege)->first();
            if (!$privilege) {
                return false;
            }
        }

        // Check directly assigned privileges
        $directPrivilege = $this->privileges()
            ->where('privileges.id', $privilege->id)
            ->when($scope, function ($query, $scope) {
                return $query->where('scope_id', $scope['id'])
                    ->where('scope_type', $scope['type']);
            })
            ->first();

        if ($directPrivilege) {
            return true;
        }

        // Check privileges through roles
        foreach ($this->roles as $role) {
            if ($role->hasPrivilege($privilege, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given privileges.
     */
    public function hasAnyPrivilege(array $privileges, $scope = null): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($privileges as $privilege) {
            if ($this->hasPrivilege($privilege, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given privileges.
     */
    public function hasAllPrivileges(array $privileges, $scope = null): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($privileges as $privilege) {
            if (!$this->hasPrivilege($privilege, $scope)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all abilities for the user (including from roles).
     */
    public function getAllAbilities()
    {
        $abilities = collect();

        // Get abilities from directly assigned privileges
        $this->privileges->each(function ($privilege) use ($abilities) {
            $abilities->push($privilege->slug);
        });

        // Get abilities from roles
        $this->roles->each(function ($role) use ($abilities) {
            $role->privileges->each(function ($privilege) use ($abilities) {
                $abilities->push($privilege->slug);
            });
        });

        return $abilities->unique()->sort();
    }

    /**
     * Get all privileges for the user (including from roles).
     */
    public function getAllPrivileges()
    {
        $privilegeIds = [];

        // Add directly assigned privileges
        $this->privileges->each(function ($privilege) use (&$privilegeIds) {
            $privilegeIds[] = $privilege->id;
        });

        // Add privileges from roles
        $this->roles->each(function ($role) use (&$privilegeIds) {
            $role->privileges->each(function ($privilege) use (&$privilegeIds) {
                $privilegeIds[] = $privilege->id;
            });
        });

        return Privilege::whereIn('id', array_unique($privilegeIds))->get();
    }

    /**
     * Grant a privilege directly to the user.
     */
    public function grantPrivilege($privilege, $grantedBy = null, $scope = null): self
    {
        if (is_string($privilege)) {
            $privilege = Privilege::where('slug', $privilege)->firstOrFail();
        }

        $pivotData = [
            'granted_by' => $grantedBy?->id ?? auth()->id(),
            'created_at' => now(),
        ];

        if ($scope) {
            $pivotData['scope_id'] = $scope['id'];
            $pivotData['scope_type'] = $scope['type'];
        }

        $this->privileges()->syncWithoutDetaching([$privilege->id => $pivotData]);

        return $this;
    }

    /**
     * Revoke a directly assigned privilege from the user.
     */
    public function revokePrivilege($privilege, $scope = null): self
    {
        if (is_string($privilege)) {
            $privilege = Privilege::where('slug', $privilege)->firstOrFail();
        }

        $query = $this->privileges()->where('privileges.id', $privilege->id);

        if ($scope) {
            $query->where('scope_id', $scope['id'])
                ->where('scope_type', $scope['type']);
        }

        $query->detach();

        return $this;
    }

    /**
     * Suspend the user.
     */
    public function suspend(?string $reason = null, ?int $durationDays = null, $suspendedBy = null): self
    {
        $this->update([
            'is_suspended' => true,
            'suspension_reason' => $reason,
            'suspended_at' => now(),
            'suspension_duration_days' => $durationDays,
        ]);

        // Revoke all tokens
        $this->revokeAllTokens('User suspended');

        // Dispatch event
        event(new UserSuspended($this, $suspendedBy ?? auth()->user(), [
            'reason' => $reason,
            'duration_days' => $durationDays,
            'via' => 'manual',
        ]));

        return $this;
    }

    /**
     * Unsuspend the user.
     */
    public function unsuspend(?string $reason = null, $unsuspendedBy = null): self
    {
        $this->update([
            'is_suspended' => false,
            'unsuspension_reason' => $reason,
            'unsuspended_at' => now(),
            'suspension_reason' => null,
            'suspended_at' => null,
            'suspension_duration_days' => null,
        ]);

        // Dispatch event
        event(new UserUnsuspended($this, $unsuspendedBy ?? auth()->user(), [
            'reason' => $reason,
            'via' => 'manual',
        ]));

        return $this;
    }

    /**
     * Lock the user account.
     */
    public function lock(?string $reason = null, $lockedBy = null): self
    {
        $this->update([
            'is_active' => false,
            'locked_reason' => $reason,
            'locked_at' => now(),
        ]);

        // Revoke all tokens
        $this->revokeAllTokens('User locked');

        return $this;
    }

    /**
     * Unlock the user account.
     */
    public function unlock(?string $reason = null, $unlockedBy = null): self
    {
        $this->update([
            'is_active' => true,
            'unlock_reason' => $reason,
            'unlocked_at' => now(),
            'locked_reason' => null,
            'locked_at' => null,
        ]);

        return $this;
    }

    /**
     * Revoke all access tokens for the user.
     */
    public function revokeAllTokens(?string $reason = null): int
    {
        $count = $this->tokens()
            ->where('revoked', false)
            ->update([
                'revoked' => true,
                'revoked_at' => now(),
                'revocation_reason' => $reason ?? 'Manual revocation',
            ]);

        return $count;
    }

    /**
     * Create a new access token for the user.
     */
    public function createToken(string $name, array $abilities = ['*'], ?Carbon $expiresAt = null)
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        // If token synchronization is enabled, sync abilities
        if (config('hyro.tokens.synchronization.enabled', true)) {
            $token->abilities = $this->getAllAbilities()->toArray();
            $token->save();
        }

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Record user login.
     */
    public function recordLogin(?string $ip = null): self
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);

        return $this;
    }

    /**
     * Check if user is currently suspended.
     */
    public function isSuspended(): bool
    {
        if (!$this->is_suspended) {
            return false;
        }

        // Check if suspension has expired
        if ($this->suspension_duration_days && $this->suspended_at) {
            $expiresAt = $this->suspended_at->addDays($this->suspension_duration_days);
            if (now()->greaterThan($expiresAt)) {
                $this->unsuspend('Suspension expired');
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user is currently locked.
     */
    public function isLocked(): bool
    {
        return !$this->is_active && $this->locked_at;
    }

    /**
     * Check if user can log in.
     */
    public function canLogin(): bool
    {
        return $this->is_active && !$this->isSuspended() && !$this->isLocked();
    }

    /**
     * Get the user's status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isLocked()) {
            return 'locked';
        }

        if ($this->isSuspended()) {
            return 'suspended';
        }

        if (!$this->is_active) {
            return 'inactive';
        }

        return 'active';
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->name);
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
                return $this->avatar;
            }

            return asset('storage/' . $this->avatar);
        }

        // Generate gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    /**
     * Check if user is online (last activity within 5 minutes).
     */
    public function getIsOnlineAttribute(): bool
    {
        if (!$this->last_login_at) {
            return false;
        }

        return $this->last_login_at->diffInMinutes(now()) <= 5;
    }

    /**
     * Generate a unique username.
     */
    public function generateUsername(): string
    {
        $base = Str::slug($this->name);
        $username = $base;
        $counter = 1;

        while (self::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Update user's password with hashing.
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    /**
     * Get the user's two-factor authentication QR code URL.
     */
    public function twoFactorQrCodeUrl(): string
    {
        return app('pragmarx.google2fa')->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }

    /**
     * Check if user has two-factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_secret;
    }

    /**
     * Verify a two-factor authentication code.
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->hasTwoFactorEnabled()) {
            return false;
        }

        return app('pragmarx.google2fa')->verifyKey($this->two_factor_secret, $code);
    }

    /**
     * Get the user's scoped privileges.
     */
    public function getScopedPrivileges($scopeType, $scopeId)
    {
        return $this->privileges()
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->get();
    }

    /**
     * Check if user has a privilege in a specific scope.
     */
    public function hasPrivilegeInScope($privilege, $scopeType, $scopeId): bool
    {
        return $this->hasPrivilege($privilege, [
            'type' => $scopeType,
            'id' => $scopeId,
        ]);
    }

    /**
     * Get the user's effective permissions (privileges + role privileges).
     */
    public function getEffectivePermissions(): array
    {
        $permissions = [
            'direct_privileges' => $this->privileges->pluck('slug')->toArray(),
            'role_privileges' => [],
            'roles' => $this->roles->pluck('slug')->toArray(),
        ];

        foreach ($this->roles as $role) {
            $permissions['role_privileges'] = array_merge(
                $permissions['role_privileges'],
                $role->privileges->pluck('slug')->toArray()
            );
        }

        $permissions['role_privileges'] = array_unique($permissions['role_privileges']);
        $permissions['all_privileges'] = array_unique(
            array_merge($permissions['direct_privileges'], $permissions['role_privileges'])
        );

        return $permissions;
    }

    /**
     * Check if user is a member of a team (example scope).
     */
    public function isMemberOf($team): bool
    {
        // This would depend on your team implementation
        // Example: return $this->teams()->where('id', $team->id)->exists();
        return false;
    }

    /**
     * Get user's notification settings.
     */
    public function getNotificationSettings(): array
    {
        return $this->metadata['notification_settings'] ?? [
            'email' => true,
            'push' => true,
            'sms' => false,
        ];
    }

    /**
     * Update user's notification settings.
     */
    public function updateNotificationSettings(array $settings): self
    {
        $metadata = $this->metadata ?? [];
        $metadata['notification_settings'] = array_merge(
            $this->getNotificationSettings(),
            $settings
        );

        $this->metadata = $metadata;
        $this->save();

        return $this;
    }

    /**
     * Search users by name, email, or username.
     */
    public static function search(string $search)
    {
        return static::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%");
    }

    /**
     * Get users with specific role.
     */
    public static function withRole($role)
    {
        if (is_string($role)) {
            return static::whereHas('roles', function ($query) use ($role) {
                $query->where('slug', $role);
            });
        }

        return static::whereHas('roles', function ($query) use ($role) {
            $query->where('id', $role->id);
        });
    }

    /**
     * Get users with specific privilege.
     */
    public static function withPrivilege($privilege)
    {
        if (is_string($privilege)) {
            return static::where(function ($query) use ($privilege) {
                $query->whereHas('privileges', function ($q) use ($privilege) {
                    $q->where('slug', $privilege);
                })->orWhereHas('roles.privileges', function ($q) use ($privilege) {
                    $q->where('slug', $privilege);
                });
            });
        }

        return static::where(function ($query) use ($privilege) {
            $query->whereHas('privileges', function ($q) use ($privilege) {
                $q->where('id', $privilege->id);
            })->orWhereHas('roles.privileges', function ($q) use ($privilege) {
                $q->where('id', $privilege->id);
            });
        });
    }

    /**
     * Impersonate this user (for admins).
     */
    public function impersonate(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $impersonator = auth()->user();

        // Check if impersonator can impersonate
        if (!$impersonator->canImpersonate()) {
            return false;
        }

        // Store original user ID in session
        session()->put('impersonator_id', $impersonator->id);

        // Log in as this user
        auth()->login($this);

        // Log the impersonation
        AuditLog::create([
            'action' => 'user_impersonated',
            'user_id' => $this->id,
            'details' => [
                'impersonator_id' => $impersonator->id,
                'impersonator_name' => $impersonator->name,
                'impersonated_at' => now(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return true;
    }

    /**
     * Check if user can impersonate others.
     */
    public function canImpersonate(): bool
    {
        return $this->is_super_admin || $this->hasPrivilege('user:impersonate');
    }

    /**
     * Check if user is being impersonated.
     */
    public function isImpersonated(): bool
    {
        return session()->has('impersonator_id');
    }

    /**
     * Stop impersonation.
     */
    public function stopImpersonating(): bool
    {
        if (!$this->isImpersonated()) {
            return false;
        }

        $impersonatorId = session()->pull('impersonator_id');
        $impersonator = static::find($impersonatorId);

        if ($impersonator) {
            auth()->login($impersonator);
            return true;
        }

        return false;
    }
}

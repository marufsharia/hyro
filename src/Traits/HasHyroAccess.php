<?php

namespace Marufsharia\Hyro\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\CacheInvalidatorContract;
use Marufsharia\Hyro\Events\UserPrivilegesChanged;
use Marufsharia\Hyro\Events\UserRolesChanged;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Marufsharia\Hyro\Models\Privilege;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\UserSuspension;

trait HasHyroAccess
{
    /**
     * Boot the trait.
     */
    protected static function bootHasHyroAccess(): void
    {
        // Invalidate cache when user is saved (roles/privileges might have changed)
        static::saved(function ($user) {
            app(CacheInvalidatorContract::class)->invalidateUserCache($user->id);
        });

        // Invalidate cache when user is deleted
        static::deleted(function ($user) {
            app(CacheInvalidatorContract::class)->invalidateUserCache($user->id);
        });
    }

    /**
     * Get all roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        $roleModel = Config::get('hyro.models.role');
        $pivotTable = Config::get('hyro.database.tables.role_user');

        return $this->belongsToMany($roleModel, $pivotTable)
            ->withPivot(['assigned_by', 'assigned_at', 'assignment_reason', 'expires_at'])
            ->withTimestamps()
            ->wherePivot(function ($query) use ($pivotTable) {
                $query->whereNull("{$pivotTable}.expires_at")
                    ->orWhere("{$pivotTable}.expires_at", '>', now());
            });
    }

    /**
     * Get all privileges the user has through roles.
     */
    public function privileges(): BelongsToMany
    {
        $privilegeModel = Config::get('hyro.models.privilege');
        $roleModel = Config::get('hyro.models.role');
        $roleUserTable = Config::get('hyro.database.tables.role_user');
        $privilegeRoleTable = Config::get('hyro.database.tables.privilege_role');

        // This is a complex many-to-many-through relationship
        // We use a custom query to join through the intermediate tables
        return $this->belongsToMany($privilegeModel, $roleUserTable, 'user_id', 'role_id')
            ->withPivot('expires_at as role_expires_at')
            ->join($privilegeRoleTable, 'role_user.role_id', '=', 'privilege_role.role_id')
            ->where(function ($query) use ($privilegeRoleTable) {
                $query->whereNull("{$privilegeRoleTable}.expires_at")
                    ->orWhere("{$privilegeRoleTable}.expires_at", '>', now());
            })
            ->select('privileges.*', 'privilege_role.expires_at as privilege_expires_at')
            ->withTimestamps();
    }

    /**
     * Get all suspensions for the user.
     */
    public function suspensions(): HasMany
    {
        $suspensionModel = Config::get('hyro.models.user_suspension');
        return $this->hasMany($suspensionModel, 'user_id');
    }

    /**
     * Get the current active suspension (if any).
     */
    public function activeSuspension(): ?UserSuspension
    {
        return $this->suspensions()
            ->active()
            ->first();
    }

    /**
     * Get cached role slugs for the user.
     */
    public function getCachedRoleSlugs(): array
    {
        $cacheKey = app(CacheInvalidatorContract::class)->getUserRolesCacheKey($this->id);
        $cacheTtl = Config::get('hyro.cache.ttl.user_roles', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->roles()
                ->pluck('slug')
                ->toArray();
        });
    }

    /**
     * Get cached privilege slugs for the user.
     */
    public function getCachedPrivilegeSlugs(): array
    {
        $cacheKey = app(CacheInvalidatorContract::class)->getUserPrivilegesCacheKey($this->id);
        $cacheTtl = Config::get('hyro.cache.ttl.user_privileges', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            $privileges = collect();

            // Get privileges from direct role assignments
            foreach ($this->roles as $role) {
                $rolePrivileges = $role->getCachedPrivilegeSlugs();
                $privileges = $privileges->merge($rolePrivileges);
            }

            // TODO: Add direct privilege assignments here when implemented
            // TODO: Add wildcard privilege expansion here

            return $privileges->unique()->values()->toArray();
        });
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        $roles = $this->getCachedRoleSlugs();
        return in_array($role, $roles);
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasRoles(array $roles): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        if (empty($roles)) {
            return true;
        }

        $userRoles = $this->getCachedRoleSlugs();
        return count(array_intersect($roles, $userRoles)) === count($roles);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        if (empty($roles)) {
            return false;
        }

        $userRoles = $this->getCachedRoleSlugs();
        return !empty(array_intersect($roles, $userRoles));
    }

    /**
     * Check if user has a specific privilege.
     */
    public function hasPrivilege(string $privilege): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        // Check fail-closed configuration
        if (Config::get('hyro.security.fail_closed', true)) {
            // Fail closed: if we can't determine, deny access
            try {
                return $this->checkPrivilege($privilege);
            } catch (\Exception $e) {
                // Log the error but deny access
                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('security_violation', $this, null, [], [
                        'description' => "Error checking privilege '{$privilege}': " . $e->getMessage(),
                        'tags' => ['fail_closed', 'privilege_check_error'],
                    ]);
                }
                return false;
            }
        } else {
            // Fail open: if we can't determine, allow access (dangerous!)
            return $this->checkPrivilege($privilege);
        }
    }

    /**
     * Internal privilege check logic.
     */
    private function checkPrivilege(string $privilege): bool
    {
        $privileges = $this->getCachedPrivilegeSlugs();

        // Exact match
        if (in_array($privilege, $privileges)) {
            return true;
        }

        // Wildcard matching
        if (Config::get('hyro.wildcards.enabled', true)) {
            foreach ($privileges as $pattern) {
                if ($this->matchesWildcard($pattern, $privilege)) {
                    return true;
                }
            }

            // Check against configured wildcard patterns
            $configuredPatterns = Config::get('hyro.wildcards.patterns', []);
            foreach (array_keys($configuredPatterns) as $pattern) {
                if ($this->matchesWildcard($pattern, $privilege)) {
                    // Check if user has this wildcard pattern
                    if (in_array($pattern, $privileges)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if a pattern matches a privilege slug.
     */
    private function matchesWildcard(string $pattern, string $privilegeSlug): bool
    {
        if (!str_contains($pattern, '*')) {
            return false;
        }

        $regexPattern = '/^' . str_replace(
                ['*', '.'],
                ['.*', '\.'],
                $pattern
            ) . '$/';

        return preg_match($regexPattern, $privilegeSlug) === 1;
    }

    /**
     * Check if user has all of the given privileges.
     */
    public function hasPrivileges(array $privileges): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        if (empty($privileges)) {
            return true;
        }

        foreach ($privileges as $privilege) {
            if (!$this->hasPrivilege($privilege)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has any of the given privileges.
     */
    public function hasAnyPrivilege(array $privileges): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        if (empty($privileges)) {
            return false;
        }

        foreach ($privileges as $privilege) {
            if ($this->hasPrivilege($privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all role slugs assigned to the user.
     */
    public function hyroRoleSlugs(): array
    {
        return $this->getCachedRoleSlugs();
    }

    /**
     * Get all privilege slugs available to the user.
     */
    public function hyroPrivilegeSlugs(): array
    {
        return $this->getCachedPrivilegeSlugs();
    }

    /**
     * Suspend the user.
     */
    public function suspend(string $reason, ?string $details = null, ?int $duration = null): void
    {
        $suspension = new UserSuspension([
            'user_id' => $this->id,
            'reason' => $reason,
            'details' => $details,
            'suspended_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($duration) {
            $suspension->suspended_until = now()->addSeconds($duration);
        }

        $suspension->save();

        // Fire event
        event(new UserSuspended($this, $suspension));

        // Invalidate cache
        app(CacheInvalidatorContract::class)->invalidateUserCache($this->id);
    }

    /**
     * Unsuspend the user.
     */
    public function unsuspend(): void
    {
        $activeSuspension = $this->activeSuspension();

        if ($activeSuspension) {
            $activeSuspension->unsuspend(auth()->id());

            // Fire event
            event(new UserUnsuspended($this, $activeSuspension));

            // Invalidate cache
            app(CacheInvalidatorContract::class)->invalidateUserCache($this->id);
        }
    }

    /**
     * Check if user is currently suspended.
     */
    public function isSuspended(): bool
    {
        return $this->activeSuspension() !== null;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $role, ?string $reason = null, ?\DateTimeInterface $expiresAt = null): void
    {
        $roleModel = Role::where('slug', $role)->firstOrFail();

        // Check if already assigned
        $existingAssignment = $this->roles()
            ->where('role_id', $roleModel->id)
            ->wherePivot(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($existingAssignment) {
            // Update existing assignment
            $this->roles()->updateExistingPivot($roleModel->id, [
                'assignment_reason' => $reason,
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]);
        } else {
            // Create new assignment
            $this->roles()->attach($roleModel->id, [
                'assigned_by' => auth()->id(),
                'assignment_reason' => $reason,
                'expires_at' => $expiresAt,
                'assigned_at' => now(),
            ]);
        }

        // Fire event
        event(new UserRolesChanged($this, ['added' => [$role]]));

        // Invalidate cache
        app(CacheInvalidatorContract::class)->invalidateUserCache($this->id);
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string $role): void
    {
        $roleModel = Role::where('slug', $role)->firstOrFail();

        // Don't remove protected roles unless forced
        if ($roleModel->is_protected && !request()->has('force')) {
            throw new \RuntimeException(
                "Cannot remove protected role '{$role}'. Use force parameter to bypass protection."
            );
        }

        $this->roles()->detach($roleModel->id);

        // Fire event
        event(new UserRolesChanged($this, ['removed' => [$role]]));

        // Invalidate cache
        app(CacheInvalidatorContract::class)->invalidateUserCache($this->id);
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(array $roles, bool $detach = true): void
    {
        $roleIds = [];
        $roleSlugs = [];

        foreach ($roles as $role) {
            $roleModel = Role::where('slug', $role)->first();

            if ($roleModel) {
                // Don't include protected roles in sync unless they're in the new list
                if ($roleModel->is_protected) {
                    $existingAssignment = $this->roles()
                        ->where('role_id', $roleModel->id)
                        ->wherePivot(function ($query) {
                            $query->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        })
                        ->exists();

                    if ($existingAssignment && !in_array($role, $roles)) {
                        // Protected role exists but not in new list - keep it
                        $roleIds[] = $roleModel->id;
                        $roleSlugs[] = $role;
                        continue;
                    }
                }

                $roleIds[] = $roleModel->id;
                $roleSlugs[] = $role;
            }
        }

        $currentRoles = $this->roles()->pluck('slug')->toArray();
        $added = array_diff($roleSlugs, $currentRoles);
        $removed = $detach ? array_diff($currentRoles, $roleSlugs) : [];

        // Perform sync
        $this->roles()->sync($roleIds);

        // Fire event
        if (!empty($added) || !empty($removed)) {
            event(new UserRolesChanged($this, [
                'added' => array_values($added),
                'removed' => array_values($removed),
            ]));
        }

        // Invalidate cache
        app(CacheInvalidatorContract::class)->invalidateUserCache($this->id);
    }

    /**
     * Get the user's identifier for audit logs.
     */
    public function getAuditIdentifier(): string
    {
        return $this->email ?? $this->id;
    }

    /**
     * Clear all cached data for this user.
     */
    public function clearHyroCache(): void
    {
        app(CacheInvalidatorContract::class)->invalidateUserCache($this->id);
    }

    /**
     * Check if user has a privilege via any method (alias for hasPrivilege).
     */
    public function canDo(string $privilege): bool
    {
        return $this->hasPrivilege($privilege);
    }

    /**
     * Check if user has a role (alias for hasRole).
     */
    public function isA(string $role): bool
    {
        return $this->hasRole($role);
    }

    /**
     * Check if user has any of the given roles (alias for hasAnyRole).
     */
    public function isAnyOf(array $roles): bool
    {
        return $this->hasAnyRole($roles);
    }
}

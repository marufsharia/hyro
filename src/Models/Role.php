<?php

namespace Marufsharia\Hyro\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\CacheInvalidatorContract;
use Marufsharia\Hyro\Events\RoleCreated;
use Marufsharia\Hyro\Events\RoleDeleted;
use Marufsharia\Hyro\Events\RoleUpdated;

class Role extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_protected',
        'is_system',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_protected' => 'boolean',
        'is_system' => 'boolean',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * The event map for the model.
     */
    protected $dispatchesEvents = [
        'created' => RoleCreated::class,
        'updated' => RoleUpdated::class,
        'deleted' => RoleDeleted::class,
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('hyro.database.tables.roles', 'hyro_roles');
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (empty($role->slug)) {
                $role->slug = str($role->name)->slug()->toString();
            }

            // Ensure slug is unique
            $originalSlug = $role->slug;
            $counter = 1;
            while (self::where('slug', $role->slug)->withTrashed()->exists()) {
                $role->slug = $originalSlug . '-' . $counter++;
            }
        });

        static::updating(function (Role $role) {
            // Prevent modification of protected roles
            if ($role->is_protected && $role->isDirty(['slug', 'is_protected', 'is_system'])) {
                throw new \RuntimeException(
                    "Cannot modify protected role '{$role->slug}'. Protected roles cannot have their slug, protection status, or system status changed."
                );
            }
        });

        static::deleting(function (Role $role) {
            // Prevent deletion of protected roles
            if ($role->is_protected && !$role->forceDeleting) {
                throw new \RuntimeException(
                    "Cannot delete protected role '{$role->slug}'. Use forceDelete() to bypass protection."
                );
            }

            // Check if this is the last admin role
            if ($role->slug === 'admin' || $role->slug === 'administrator') {
                $adminRoleCount = self::whereIn('slug', ['admin', 'administrator'])
                    ->where('id', '!=', $role->id)
                    ->count();

                $minAdmins = Config::get('hyro.security.min_admins', 1);
                if ($adminRoleCount < $minAdmins) {
                    throw new \RuntimeException(
                        "Cannot delete role '{$role->slug}'. This would leave less than {$minAdmins} admin roles in the system."
                    );
                }
            }
        });

        static::deleted(function (Role $role) {
            // Invalidate cache
            if (app()->bound(CacheInvalidatorContract::class)) {
                app(CacheInvalidatorContract::class)->invalidateRoleCache($role->id);
            }
        });

        static::saved(function (Role $role) {
            // Invalidate cache on save
            if (app()->bound(CacheInvalidatorContract::class)) {
                app(CacheInvalidatorContract::class)->invalidateRoleCache($role->id);
            }
        });
    }

    /**
     * Get all users assigned to this role.
     */
    public function users(): BelongsToMany
    {
        $userModel = Config::get('hyro.models.user', \App\Models\User::class);
        $pivotTable = Config::get('hyro.database.tables.role_user', 'hyro_role_user');

        return $this->belongsToMany($userModel, $pivotTable)
            ->withPivot(['assigned_by', 'assigned_at', 'assignment_reason', 'expires_at'])
            ->withTimestamps()
            ->where(function ($query) use ($pivotTable) {
                $query->whereNull("{$pivotTable}.expires_at")
                    ->orWhere("{$pivotTable}.expires_at", '>', now());
            });
    }

    /**
     * Get all privileges assigned to this role.
     */
    public function privileges(): BelongsToMany
    {
        $privilegeModel = Config::get('hyro.models.privilege', \Marufsharia\Hyro\Models\Privilege::class);
        $pivotTable = Config::get('hyro.database.tables.privilege_role', 'hyro_privilege_role');

        return $this->belongsToMany($privilegeModel, $pivotTable)
            ->withPivot(['granted_by', 'granted_at', 'grant_reason', 'conditions', 'expires_at'])
            ->withTimestamps()
            ->where(function ($query) use ($pivotTable) {
                $query->whereNull("{$pivotTable}.expires_at")
                    ->orWhere("{$pivotTable}.expires_at", '>', now());
            });
    }

    /**
     * Get cached privilege slugs for this role.
     */
    public function getCachedPrivilegeSlugs(): array
    {
        if (!app()->bound(CacheInvalidatorContract::class)) {
            return $this->privileges()->pluck('slug')->toArray();
        }

        $cacheKey = app(CacheInvalidatorContract::class)->getRolePrivilegesCacheKey($this->id);
        $cacheTtl = Config::get('hyro.cache.ttl.role_privileges', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->privileges()
                ->pluck('slug')
                ->toArray();
        });
    }

    /**
     * Grant a privilege to this role.
     */
    public function grantPrivilege(string $privilegeSlug, ?string $reason = null, ?array $conditions = null, ?\DateTimeInterface $expiresAt = null): void
    {
        $privilegeModel = Config::get('hyro.models.privilege', \Marufsharia\Hyro\Models\Privilege::class);

        $privilege = $privilegeModel::firstOrCreate(
            ['slug' => $privilegeSlug],
            [
                'name' => str($privilegeSlug)->replace('.', ' ')->title(),
                'description' => "Auto-generated privilege: {$privilegeSlug}",
            ]
        );

        $this->privileges()->syncWithoutDetaching([
            $privilege->id => [
                'grant_reason' => $reason,
                'conditions' => $conditions ? json_encode($conditions) : null,
                'expires_at' => $expiresAt,
                'granted_at' => now(),
            ]
        ]);

        // Invalidate caches
        if (app()->bound(CacheInvalidatorContract::class)) {
            app(CacheInvalidatorContract::class)->invalidateRoleCache($this->id);
        }
    }

    /**
     * Revoke a privilege from this role.
     */
    public function revokePrivilege(string $privilegeSlug): void
    {
        $privilegeModel = Config::get('hyro.models.privilege', \Marufsharia\Hyro\Models\Privilege::class);
        $privilege = $privilegeModel::where('slug', $privilegeSlug)->first();

        if ($privilege) {
            $this->privileges()->detach($privilege->id);

            if (app()->bound(CacheInvalidatorContract::class)) {
                app(CacheInvalidatorContract::class)->invalidateRoleCache($this->id);
            }
        }
    }

    /**
     * Scope: Get only protected roles.
     */
    public function scopeProtected(Builder $query): Builder
    {
        return $query->where('is_protected', true);
    }

    /**
     * Scope: Get only system roles.
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope: Get only non-expired role assignments for a user.
     */
    public function scopeActiveForUser(Builder $query, $userId): Builder
    {
        $pivotTable = Config::get('hyro.database.tables.role_user', 'hyro_role_user');

        return $query->whereHas('users', function ($q) use ($userId, $pivotTable) {
            $q->where('users.id', $userId)
                ->where(function ($q) use ($pivotTable) {
                    $q->whereNull("{$pivotTable}.expires_at")
                        ->orWhere("{$pivotTable}.expires_at", '>', now());
                });
        });
    }

    /**
     * Check if role has a specific privilege.
     */
    public function hasPrivilege(string $privilegeSlug): bool
    {
        $privileges = $this->getCachedPrivilegeSlugs();

        // Exact match
        if (in_array($privilegeSlug, $privileges)) {
            return true;
        }

        // Wildcard matching
        if (Config::get('hyro.wildcards.enabled', true)) {
            foreach ($privileges as $pattern) {
                if ($this->matchesWildcard($pattern, $privilegeSlug)) {
                    return true;
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
}

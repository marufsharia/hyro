<?php

namespace Marufsharia\Hyro\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\CacheInvalidatorContract;
use Marufsharia\Hyro\Events\PrivilegeCreated;
use Marufsharia\Hyro\Events\PrivilegeDeleted;
use Marufsharia\Hyro\Events\PrivilegeUpdated;
use Marufsharia\Hyro\Support\Traits\HasUuid;

class Privilege extends Model
{
    use SoftDeletes;
    use HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_wildcard',
        'wildcard_pattern',
        'category',
        'priority',
        'is_protected',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_wildcard' => 'boolean',
        'is_protected' => 'boolean',
        'priority' => 'integer',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * The event map for the model.
     */
    protected $dispatchesEvents = [
        'created' => PrivilegeCreated::class,
        'updated' => PrivilegeUpdated::class,
        'deleted' => PrivilegeDeleted::class,
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return Config::get('hyro.database.tables.privileges', parent::getTable());
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Privilege $privilege) {
            // Auto-detect wildcard
            if ($privilege->is_wildcard === null) {
                $privilege->is_wildcard = str_contains($privilege->slug, '*');
            }

            // Set wildcard pattern
            if ($privilege->is_wildcard && empty($privilege->wildcard_pattern)) {
                $privilege->wildcard_pattern = $privilege->slug;
            }

            // Set default name if not provided
            if (empty($privilege->name)) {
                $privilege->name = str($privilege->slug)
                    ->replace('.', ' ')
                    ->replace('*', ' (any)')
                    ->title()
                    ->toString();
            }

            // Ensure slug is unique
            $originalSlug = $privilege->slug;
            $counter = 1;
            while (self::where('slug', $privilege->slug)->withTrashed()->exists()) {
                $privilege->slug = $originalSlug . '-' . $counter++;
            }
        });

        static::updating(function (Privilege $privilege) {
            // Prevent modification of protected privileges
            if ($privilege->is_protected && $privilege->isDirty(['slug', 'is_protected'])) {
                throw new \RuntimeException(
                    "Cannot modify protected privilege '{$privilege->slug}'. Protected privileges cannot have their slug or protection status changed."
                );
            }

            // Validate wildcard pattern
            if ($privilege->is_wildcard && !str_contains($privilege->wildcard_pattern, '*')) {
                throw new \RuntimeException(
                    "Wildcard privilege '{$privilege->slug}' must have a wildcard pattern containing '*'"
                );
            }
        });

        static::deleting(function (Privilege $privilege) {
            // Prevent deletion of protected privileges
            if ($privilege->is_protected && !$privilege->forceDeleting) {
                throw new \RuntimeException(
                    "Cannot delete protected privilege '{$privilege->slug}'. Use forceDelete() to bypass protection."
                );
            }
        });

        static::deleted(function (Privilege $privilege) {
            // Invalidate cache
            app(CacheInvalidatorContract::class)->invalidatePrivilegeCache($privilege->id);
        });

        static::saved(function (Privilege $privilege) {
            // Invalidate cache on save
            app(CacheInvalidatorContract::class)->invalidatePrivilegeCache($privilege->id);
        });
    }

    /**
     * Get all roles that have this privilege.
     */
    public function roles(): BelongsToMany
    {
        $roleModel = Config::get('hyro.models.role');
        $pivotTable = Config::get('hyro.database.tables.privilege_role');

        return $this->belongsToMany($roleModel, $pivotTable)
            ->withPivot(['granted_by', 'granted_at', 'grant_reason', 'conditions', 'expires_at'])
            ->withTimestamps()
            ->wherePivotNull('expires_at')
            ->orWherePivot('expires_at', '>', now());
    }

    /**
     * Get all users who have this privilege through their roles.
     */
    public function users(): BelongsToMany
    {
        $userModel = Config::get('hyro.models.user');
        $roleModel = Config::get('hyro.models.role');
        $privilegeRoleTable = Config::get('hyro.database.tables.privilege_role');
        $roleUserTable = Config::get('hyro.database.tables.role_user');

        // This is a many-to-many-through relationship simulation
        return $this->belongsToMany($userModel, $privilegeRoleTable, 'privilege_id', 'role_id')
            ->withPivot(['granted_by', 'granted_at', 'grant_reason', 'conditions', 'expires_at as privilege_expires_at'])
            ->wherePivot(function ($query) use ($privilegeRoleTable) {
                $query->whereNull("{$privilegeRoleTable}.expires_at")
                    ->orWhere("{$privilegeRoleTable}.expires_at", '>', now());
            })
            ->withTimestamps();
    }

    /**
     * Check if this privilege matches a given slug (handles wildcards).
     */
    public function matches(string $privilegeSlug): bool
    {
        // Exact match
        if ($this->slug === $privilegeSlug) {
            return true;
        }

        // Wildcard match
        if ($this->is_wildcard && $this->wildcard_pattern) {
            $regexPattern = '/^' . str_replace(
                    ['*', '.'],
                    ['.*', '\.'],
                    $this->wildcard_pattern
                ) . '$/';

            return preg_match($regexPattern, $privilegeSlug) === 1;
        }

        return false;
    }

    /**
     * Scope: Get only wildcard privileges.
     */
    public function scopeWildcards(Builder $query): Builder
    {
        return $query->where('is_wildcard', true);
    }

    /**
     * Scope: Get privileges by category.
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Get privileges that match a slug (including wildcards).
     */
    public function scopeMatching(Builder $query, string $privilegeSlug): Builder
    {
        return $query->where(function ($q) use ($privilegeSlug) {
            // Exact match
            $q->where('slug', $privilegeSlug);

            // Wildcard matches
            if (Config::get('hyro.wildcards.enabled', true)) {
                $wildcards = self::wildcards()->get();
                foreach ($wildcards as $wildcard) {
                    if ($wildcard->matches($privilegeSlug)) {
                        $q->orWhere('id', $wildcard->id);
                    }
                }
            }
        });
    }

    /**
     * Get all privilege slugs that match a given pattern (for wildcard expansion).
     */
    public static function expandWildcard(string $pattern): array
    {
        if (!str_contains($pattern, '*')) {
            return [$pattern];
        }

        $cacheKey = 'hyro:wildcard:expansion:' . md5($pattern);
        $cacheTtl = Config::get('hyro.cache.ttl.wildcard_resolution', 300);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($pattern) {
            $regexPattern = '/^' . str_replace(
                    ['*', '.'],
                    ['.*', '\.'],
                    $pattern
                ) . '$/';

            return self::where('slug', 'regexp', $regexPattern)
                ->orWhere('wildcard_pattern', 'regexp', $regexPattern)
                ->pluck('slug')
                ->toArray();
        });
    }
}

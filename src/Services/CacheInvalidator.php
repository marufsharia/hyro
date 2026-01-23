<?php

namespace Marufsharia\Hyro\Services;

use Marufsharia\Hyro\Contracts\CacheInvalidatorContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheInvalidator implements CacheInvalidatorContract
{
    /**
     * Cache key prefixes
     */
    protected array $prefixes = [
        'user_roles' => 'hyro.user.roles.',
        'user_privileges' => 'hyro.user.privileges.',
        'role_privileges' => 'hyro.role.privileges.',
        'user_all' => 'hyro.user.all.',
        'role_all' => 'hyro.role.all.',
    ];

    /**
     * Cache duration in seconds
     */
    protected int $cacheDuration = 3600; // 1 hour

    /**
     * Whether to use cache tags (requires a cache driver that supports tags like Redis/Memcached)
     */
    protected bool $useTags = false;

    /**
     * Create a new cache invalidator instance.
     */
    public function __construct()
    {
        $this->useTags = config('hyro.cache.use_tags', false);
        $this->cacheDuration = config('hyro.cache.duration', 3600);
    }

    /**
     * Invalidate all cache entries for a user.
     */
    public function invalidateUserCache($userId): void
    {
        try {
            if ($this->useTags) {
                Cache::tags(['hyro', "user.{$userId}"])->flush();
            } else {
                // Invalidate specific user-related cache keys
                Cache::forget($this->getUserRolesCacheKey($userId));
                Cache::forget($this->getUserPrivilegesCacheKey($userId));
                Cache::forget("hyro.user.{$userId}.permissions");
                Cache::forget("hyro.user.{$userId}.abilities");
                Cache::forget("hyro.user.{$userId}.status");

                // Also invalidate pattern-based keys
                $this->invalidatePattern("hyro.user.{$userId}.*");
            }

            Log::debug('User cache invalidated', ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate user cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate all cache entries for a role.
     */
    public function invalidateRoleCache($roleId): void
    {
        try {
            if ($this->useTags) {
                Cache::tags(['hyro', "role.{$roleId}"])->flush();
            } else {
                // Invalidate role-specific cache
                Cache::forget($this->getRolePrivilegesCacheKey($roleId));
                Cache::forget("hyro.role.{$roleId}.users");
                Cache::forget("hyro.role.{$roleId}.details");

                // Also invalidate all users who have this role
                // This might be expensive, so we'll do it in a job if needed
                $this->invalidatePattern("hyro.role.{$roleId}.*");
            }

            Log::debug('Role cache invalidated', ['role_id' => $roleId]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate role cache', [
                'role_id' => $roleId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate all cache entries for a privilege.
     */
    public function invalidatePrivilegeCache($privilegeId): void
    {
        try {
            if ($this->useTags) {
                Cache::tags(['hyro', "privilege.{$privilegeId}"])->flush();
            } else {
                // Privilege changes affect multiple roles and users
                // We'll use a broader invalidation pattern
                Cache::forget("hyro.privilege.{$privilegeId}.roles");
                Cache::forget("hyro.privilege.{$privilegeId}.details");

                // Since privilege changes affect many users, we might want to clear all Hyro cache
                // or use a more sophisticated pattern
                $this->invalidatePattern("hyro.privilege.{$privilegeId}.*");

                // Also invalidate all roles that might have this privilege
                // This is a more expensive operation
                $this->invalidateRolePrivilegesCaches();
            }

            Log::debug('Privilege cache invalidated', ['privilege_id' => $privilegeId]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate privilege cache', [
                'privilege_id' => $privilegeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate all cache entries.
     */
    public function invalidateAllCache(): void
    {
        try {
            if ($this->useTags) {
                Cache::tags(['hyro'])->flush();
            } else {
                // Invalidate all Hyro-related cache keys
                $this->invalidatePattern('hyro.*');
            }

            Log::info('All Hyro cache invalidated');
        } catch (\Exception $e) {
            Log::error('Failed to invalidate all cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cache key for user roles.
     */
    public function getUserRolesCacheKey($userId): string
    {
        return $this->prefixes['user_roles'] . $userId;
    }

    /**
     * Get cache key for user privileges.
     */
    public function getUserPrivilegesCacheKey($userId): string
    {
        return $this->prefixes['user_privileges'] . $userId;
    }

    /**
     * Get cache key for role privileges.
     */
    public function getRolePrivilegesCacheKey($roleId): string
    {
        return $this->prefixes['role_privileges'] . $roleId;
    }

    /**
     * Cache user roles with optional tags.
     */
    public function cacheUserRoles($userId, $roles, ?int $duration = null): bool
    {
        $key = $this->getUserRolesCacheKey($userId);
        $duration = $duration ?? $this->cacheDuration;

        if ($this->useTags) {
            return Cache::tags(['hyro', "user.{$userId}"])->put($key, $roles, $duration);
        }

        return Cache::put($key, $roles, $duration);
    }

    /**
     * Get cached user roles.
     */
    public function getCachedUserRoles($userId)
    {
        $key = $this->getUserRolesCacheKey($userId);
        return Cache::get($key);
    }

    /**
     * Cache user privileges with optional tags.
     */
    public function cacheUserPrivileges($userId, $privileges, ?int $duration = null): bool
    {
        $key = $this->getUserPrivilegesCacheKey($userId);
        $duration = $duration ?? $this->cacheDuration;

        if ($this->useTags) {
            return Cache::tags(['hyro', "user.{$userId}"])->put($key, $privileges, $duration);
        }

        return Cache::put($key, $privileges, $duration);
    }

    /**
     * Get cached user privileges.
     */
    public function getCachedUserPrivileges($userId)
    {
        $key = $this->getUserPrivilegesCacheKey($userId);
        return Cache::get($key);
    }

    /**
     * Cache role privileges with optional tags.
     */
    public function cacheRolePrivileges($roleId, $privileges, ?int $duration = null): bool
    {
        $key = $this->getRolePrivilegesCacheKey($roleId);
        $duration = $duration ?? $this->cacheDuration;

        if ($this->useTags) {
            return Cache::tags(['hyro', "role.{$roleId}"])->put($key, $privileges, $duration);
        }

        return Cache::put($key, $privileges, $duration);
    }

    /**
     * Get cached role privileges.
     */
    public function getCachedRolePrivileges($roleId)
    {
        $key = $this->getRolePrivilegesCacheKey($roleId);
        return Cache::get($key);
    }

    /**
     * Check if user roles are cached.
     */
    public function hasUserRolesCache($userId): bool
    {
        $key = $this->getUserRolesCacheKey($userId);
        return Cache::has($key);
    }

    /**
     * Check if user privileges are cached.
     */
    public function hasUserPrivilegesCache($userId): bool
    {
        $key = $this->getUserPrivilegesCacheKey($userId);
        return Cache::has($key);
    }

    /**
     * Check if role privileges are cached.
     */
    public function hasRolePrivilegesCache($roleId): bool
    {
        $key = $this->getRolePrivilegesCacheKey($roleId);
        return Cache::has($key);
    }

    /**
     * Invalidate cache for a specific pattern (for drivers that support it).
     */
    protected function invalidatePattern(string $pattern): void
    {
        // This method depends on the cache driver
        // Redis supports this natively, but Laravel's cache doesn't have a built-in method
        // We'll implement it only if using Redis
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } catch (\Exception $e) {
                // Silently fail - pattern matching might not be supported
            }
        }
    }

    /**
     * Invalidate all role privileges caches.
     */
    protected function invalidateRolePrivilegesCaches(): void
    {
        // This is an expensive operation
        // We might want to queue it for large systems
        $this->invalidatePattern($this->prefixes['role_privileges'] . '*');
    }

    /**
     * Invalidate cache on role assignment.
     */
    public function onRoleAssigned($userId, $roleId): void
    {
        $this->invalidateUserCache($userId);
        $this->invalidateRoleCache($roleId);
    }

    /**
     * Invalidate cache on role revocation.
     */
    public function onRoleRevoked($userId, $roleId): void
    {
        $this->invalidateUserCache($userId);
        $this->invalidateRoleCache($roleId);
    }

    /**
     * Invalidate cache on privilege granted to role.
     */
    public function onPrivilegeGrantedToRole($roleId, $privilegeId): void
    {
        $this->invalidateRoleCache($roleId);
        $this->invalidatePrivilegeCache($privilegeId);

        // Also invalidate all users with this role
        $this->invalidatePattern("hyro.user.*.roles.{$roleId}");
    }

    /**
     * Invalidate cache on privilege revoked from role.
     */
    public function onPrivilegeRevokedFromRole($roleId, $privilegeId): void
    {
        $this->invalidateRoleCache($roleId);
        $this->invalidatePrivilegeCache($privilegeId);

        // Also invalidate all users with this role
        $this->invalidatePattern("hyro.user.*.roles.{$roleId}");
    }

    /**
     * Get cache statistics.
     */
    public function getStats(): array
    {
        $stats = [
            'cache_driver' => config('cache.default'),
            'use_tags' => $this->useTags,
            'cache_duration' => $this->cacheDuration,
        ];

        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $stats['redis_memory_usage'] = $redis->info('memory')['used_memory_human'] ?? 'N/A';
                $stats['redis_keys_count'] = $redis->dbsize();
            } catch (\Exception $e) {
                $stats['redis_error'] = $e->getMessage();
            }
        }

        return $stats;
    }
}

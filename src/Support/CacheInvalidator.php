<?php

namespace Marufsharia\Hyro\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\CacheInvalidatorContract;

class CacheInvalidator implements CacheInvalidatorContract
{
    /**
     * {@inheritdoc}
     */
    public function invalidateUserCache($userId): void
    {
        $keys = [
            $this->getUserRolesCacheKey($userId),
            $this->getUserPrivilegesCacheKey($userId),
        ];

        Cache::deleteMultiple($keys);

        // Also invalidate any related caches
        $pattern = Config::get('hyro.cache.prefix', 'hyro:') . "users:{$userId}:*";
        $this->invalidateByPattern($pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateRoleCache($roleId): void
    {
        $key = $this->getRolePrivilegesCacheKey($roleId);
        Cache::forget($key);

        // Invalidate all users who have this role
        $pattern = Config::get('hyro.cache.prefix', 'hyro:') . "users:*:roles";
        $this->invalidateByPattern($pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidatePrivilegeCache($privilegeId): void
    {
        // Invalidate all roles that have this privilege
        $pattern = Config::get('hyro.cache.prefix', 'hyro:') . "role:*:privileges";
        $this->invalidateByPattern($pattern);

        // Invalidate all users
        $pattern = Config::get('hyro.cache.prefix', 'hyro:') . "users:*:privileges";
        $this->invalidateByPattern($pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateAllCache(): void
    {
        $pattern = Config::get('hyro.cache.prefix', 'hyro:') . '*';
        $this->invalidateByPattern($pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserRolesCacheKey($userId): string
    {
        return Config::get('hyro.cache.prefix', 'hyro:') . "users:{$userId}:roles";
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPrivilegesCacheKey($userId): string
    {
        return Config::get('hyro.cache.prefix', 'hyro:') . "users:{$userId}:privileges";
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePrivilegesCacheKey($roleId): string
    {
        return Config::get('hyro.cache.prefix', 'hyro:') . "role:{$roleId}:privileges";
    }

    /**
     * Invalidate cache by pattern.
     */
    private function invalidateByPattern(string $pattern): void
    {
        // This implementation depends on your cache driver
        // Redis supports SCAN and DEL by pattern
        // For other drivers, you might need a different approach

        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();

                // Use SCAN to find all matching keys
                $cursor = 0;
                do {
                    list($cursor, $keys) = $redis->scan($cursor, 'MATCH', $pattern, 'COUNT', 100);

                    if (!empty($keys)) {
                        $redis->del(...$keys);
                    }
                } while ($cursor > 0);
            }
        } catch (\Exception $e) {
            // Log error but don't crash
            if (Config::get('hyro.auditing.enabled', true)) {
                // Log to audit if enabled
            }
        }
    }
}

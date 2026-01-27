<?php

namespace Marufsharia\Hyro\Contracts;

interface CacheInvalidatorContract
{
    /**
     * Invalidate all cache entries for a users.
     */
    public function invalidateUserCache($userId): void;

    /**
     * Invalidate all cache entries for a role.
     */
    public function invalidateRoleCache($roleId): void;

    /**
     * Invalidate all cache entries for a privilege.
     */
    public function invalidatePrivilegeCache($privilegeId): void;

    /**
     * Invalidate all cache entries.
     */
    public function invalidateAllCache(): void;

    /**
     * Get cache key for users roles.
     */
    public function getUserRolesCacheKey($userId): string;

    /**
     * Get cache key for users privileges.
     */
    public function getUserPrivilegesCacheKey($userId): string;

    /**
     * Get cache key for role privileges.
     */
    public function getRolePrivilegesCacheKey($roleId): string;
}

<?php

namespace Marufsharia\Hyro\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Marufsharia\Hyro\HyroManager driver(string $driver = null)
 * @method static \Marufsharia\Hyro\Repositories\UserRepository user()
 * @method static \Marufsharia\Hyro\Repositories\RoleRepository role()
 * @method static \Marufsharia\Hyro\Repositories\PrivilegeRepository privilege()
 * @method static \Marufsharia\Hyro\Repositories\AuditRepository audit()
 * @method static bool install()
 * @method static bool uninstall(bool $force = false)
 * @method static array status()
 * @method static void suspendUser(int $userId, string $reason)
 * @method static void unsuspendUser(int $userId)
 * @method static array getRoles()
 * @method static array getPrivileges()
 * @method static void assignRole(int $userId, string $role)
 * @method static void revokeRole(int $userId, string $role)
 * @method static void assignPrivilege(int $roleId, string $privilege)
 * @method static void revokePrivilege(int $roleId, string $privilege)
 * @method static void clearCache()
 * @method static void emergencyLockdown()
 * @method static void emergencyUnlock()
 * @method static bool isLocked()
 *
 * @see \Marufsharia\Hyro\HyroManager
 */
class Hyro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hyro';
    }

    /**
     * Get sidebar items
     */
    public static function sidebar(): array
    {
        // Use SidebarRegistry from admin-panel package
        if (app()->bound(\Marufsharia\Hyro\AdminPanel\Services\SidebarRegistry::class)) {
            return app(\Marufsharia\Hyro\AdminPanel\Services\SidebarRegistry::class)::items();
        }

        // Fallback: return empty array
        return [];
    }
}


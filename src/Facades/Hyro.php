<?php

namespace MarufSharia\Hyro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MarufSharia\Hyro\HyroManager driver(string $driver = null)
 * @method static \MarufSharia\Hyro\Contracts\UserContract user()
 * @method static \MarufSharia\Hyro\Models\Role role()
 * @method static \MarufSharia\Hyro\Models\Privilege privilege()
 * @method static \MarufSharia\Hyro\Contracts\AuditLogger audit()
 * @method static bool install()
 * @method static bool uninstall(bool $force = false)
 * @method static array status()
 * @method static void suspendUser(int $userId, string $reason)
 * @method static void unsuspendUser(int $userId)
 * @method static array getRoles()
 * @method static array getPrivileges()
 * @method static void assignRole(int $userId, string $role)
 * @method static void revokeRole(int $userId, string $role)
 * @method static void assignPrivilege(int $userId, string $privilege)
 * @method static void revokePrivilege(int $userId, string $privilege)
 * @method static void clearCache()
 * @method static void emergencyLockdown()
 * @method static void emergencyUnlock()
 * 
 * @see \MarufSharia\Hyro\HyroManager
 */
class Hyro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hyro';
    }
}
<?php

namespace MarufSharia\Hyro;

use Illuminate\Support\Manager;
use Illuminate\Support\Facades\Schema;
use Marufsharia\Hyro\Core\Drivers\SanctumDriver;
use Marufsharia\Hyro\Core\Support\Hooks\HookManager;

/**
 * Class HyroManager
 *
 * Central access point for Hyro configuration, drivers,
 * authorization resources, and lifecycle operations.
 *
 * This class follows Laravel's Manager pattern and delegates
 * all behavior to the active driver implementation.
 */
class HyroManager extends Manager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The hook manager instance.
     *
     * @var \Marufsharia\Hyro\Support\Hooks\HookManager
     */
    protected $hookManager;

    /**
     * Create a new HyroManager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->hookManager = $app->make(HookManager::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration Access
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve a Hyro configuration value using dot notation.
     *
     * When no key is provided, the entire Hyro configuration
     * array will be returned.
     *
     * @param  string|null  $key
     * @param  mixed|null   $default
     * @return mixed
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('hyro');
        }

        return data_get(config('hyro'), $key, $default);
    }

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether Hyro is globally enabled.
     */
    public function enabled(): bool
    {
        return (bool) $this->config('enabled', true);
    }

    /**
     * Determine whether the Hyro API is enabled.
     */
    public function apiEnabled(): bool
    {
        return (bool) $this->config('api.enabled', false);
    }

    /**
     * Determine whether the Hyro admin panel is enabled.
     */
    public function adminEnabled(): bool
    {
        return (bool) $this->config('admin.enabled', true);
    }

    /**
     * Determine whether Hyro authentication features are enabled.
     */
    public function authEnabled(): bool
    {
        return (bool) $this->config('auth.enabled', true);
    }

    /**
     * Determine whether Hyro should fail closed on authorization errors.
     */
    public function failClosed(): bool
    {
        return (bool) $this->config('security.fail_closed', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Routing Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the admin route prefix.
     */
    public function adminPrefix(): string
    {
        return (string) $this->config('admin.route.prefix', 'admin/hyro');
    }

    /**
     * Get the API route prefix.
     */
    public function apiPrefix(): string
    {
        return (string) $this->config('api.prefix', 'api/hyro');
    }

    /*
    |--------------------------------------------------------------------------
    | Database Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the configured database table name.
     *
     * @param  string  $key
     */
    public function table(string $key): string
    {
        return (string) $this->config("database.tables.{$key}");
    }

    /**
     * Get the configured model class name.
     *
     * @param  string  $key
     */
    public function model(string $key): string
    {
        return (string) $this->config("database.models.{$key}");
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Resolution
    |--------------------------------------------------------------------------
    */

    /**
     * Get the default Hyro driver name.
     */
    public function getDefaultDriver(): string
    {
        return (string) config('hyro.driver', 'sanctum');
    }

    /**
     * Create the Sanctum driver instance.
     */
    protected function createSanctumDriver(): SanctumDriver
    {
        return new SanctumDriver(
            $this->app['config']->get('hyro')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Domain Resource Access
    |--------------------------------------------------------------------------
    */

    /**
     * Access the users repository.
     */
    public function user()
    {
        return $this->driver()->user();
    }

    /**
     * Access the role repository.
     */
    public function role()
    {
        return $this->driver()->role();
    }

    /**
     * Access the privilege repository.
     */
    public function privilege()
    {
        return $this->driver()->privilege();
    }

    /**
     * Access the audit repository.
     */
    public function audit()
    {
        return $this->driver()->audit();
    }

    /*
    |--------------------------------------------------------------------------
    | Installation Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Install Hyro resources (tables, defaults, etc).
     */
    public function install(): bool
    {
        return $this->driver()->install();
    }

    /**
     * Uninstall Hyro resources.
     *
     * @param  bool  $force  Force removal even if protected
     */
    public function uninstall(bool $force = false): bool
    {
        return $this->driver()->uninstall($force);
    }

    /*
    |--------------------------------------------------------------------------
    | System Status
    |--------------------------------------------------------------------------
    */

    /**
     * Get the current Hyro system status.
     */
    public function status(): array
    {
        return [
            'installed' => Schema::hasTable(
                $this->table('roles') ?: 'hyro_roles'
            ),

            'features' => $this->config('admin.features', []),

            'tables' => [
                'users' => $this->config('database.tables.users'),
                'roles' => $this->config('database.tables.roles'),
                'privileges' => $this->config('database.tables.privileges'),
            ],

            'driver' => $this->getDefaultDriver(),

            'version' => $this->config('version', '1.0.0-alpha'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User Management Convenience Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Suspend a user with a reason.
     *
     * @param  int  $userId
     * @param  string  $reason
     * @return void
     */
    public function suspendUser(int $userId, string $reason): void
    {
        $suspensionModel = $this->config('database.models.user_suspension', \Marufsharia\Hyro\Models\UserSuspension::class);
        
        $suspensionModel::create([
            'user_id' => $userId,
            'reason' => $reason,
            'suspended_at' => now(),
            'suspended_by' => auth()->id(),
        ]);

        // Log the action
        $this->audit()->log([
            'action' => 'user.suspended',
            'model_type' => $this->config('database.models.user'),
            'model_id' => $userId,
            'new_values' => ['reason' => $reason],
        ]);
    }

    /**
     * Unsuspend a user.
     *
     * @param  int  $userId
     * @return void
     */
    public function unsuspendUser(int $userId): void
    {
        $suspensionModel = $this->config('database.models.user_suspension', \Marufsharia\Hyro\Models\UserSuspension::class);
        
        $suspensionModel::where('user_id', $userId)
            ->whereNull('unsuspended_at')
            ->update([
                'unsuspended_at' => now(),
                'unsuspended_by' => auth()->id(),
            ]);

        // Log the action
        $this->audit()->log([
            'action' => 'user.unsuspended',
            'model_type' => $this->config('database.models.user'),
            'model_id' => $userId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Role & Privilege Convenience Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get all roles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->role()->all()->toArray();
    }

    /**
     * Get all privileges.
     *
     * @return array
     */
    public function getPrivileges(): array
    {
        return $this->privilege()->all()->toArray();
    }

    /**
     * Assign a role to a user.
     *
     * @param  int  $userId
     * @param  string  $role  Role slug or ID
     * @return void
     */
    public function assignRole(int $userId, string $role): void
    {
        // Check if role is slug or ID
        if (is_numeric($role)) {
            $roleId = (int) $role;
        } else {
            $roleModel = $this->role()->findBySlug($role);
            if (!$roleModel) {
                throw new \InvalidArgumentException("Role '{$role}' not found.");
            }
            $roleId = $roleModel->id;
        }

        $this->user()->assignRole($userId, $roleId);

        // Log the action
        $this->audit()->log([
            'action' => 'role.assigned',
            'model_type' => $this->config('database.models.user'),
            'model_id' => $userId,
            'new_values' => ['role' => $role],
        ]);
    }

    /**
     * Revoke a role from a user.
     *
     * @param  int  $userId
     * @param  string  $role  Role slug or ID
     * @return void
     */
    public function revokeRole(int $userId, string $role): void
    {
        // Check if role is slug or ID
        if (is_numeric($role)) {
            $roleId = (int) $role;
        } else {
            $roleModel = $this->role()->findBySlug($role);
            if (!$roleModel) {
                throw new \InvalidArgumentException("Role '{$role}' not found.");
            }
            $roleId = $roleModel->id;
        }

        $this->user()->removeRole($userId, $roleId);

        // Log the action
        $this->audit()->log([
            'action' => 'role.revoked',
            'model_type' => $this->config('database.models.user'),
            'model_id' => $userId,
            'old_values' => ['role' => $role],
        ]);
    }

    /**
     * Assign a privilege to a role.
     *
     * @param  int  $roleId
     * @param  string  $privilege  Privilege slug or ID
     * @return void
     */
    public function assignPrivilege(int $roleId, string $privilege): void
    {
        // Check if privilege is slug or ID
        if (is_numeric($privilege)) {
            $privilegeId = (int) $privilege;
        } else {
            $privilegeModel = $this->privilege()->findBySlug($privilege);
            if (!$privilegeModel) {
                throw new \InvalidArgumentException("Privilege '{$privilege}' not found.");
            }
            $privilegeId = $privilegeModel->id;
        }

        $this->role()->assignPrivilege($roleId, $privilegeId);

        // Log the action
        $this->audit()->log([
            'action' => 'privilege.assigned',
            'model_type' => $this->config('database.models.role'),
            'model_id' => $roleId,
            'new_values' => ['privilege' => $privilege],
        ]);
    }

    /**
     * Revoke a privilege from a role.
     *
     * @param  int  $roleId
     * @param  string  $privilege  Privilege slug or ID
     * @return void
     */
    public function revokePrivilege(int $roleId, string $privilege): void
    {
        // Check if privilege is slug or ID
        if (is_numeric($privilege)) {
            $privilegeId = (int) $privilege;
        } else {
            $privilegeModel = $this->privilege()->findBySlug($privilege);
            if (!$privilegeModel) {
                throw new \InvalidArgumentException("Privilege '{$privilege}' not found.");
            }
            $privilegeId = $privilegeModel->id;
        }

        $this->role()->removePrivilege($roleId, $privilegeId);

        // Log the action
        $this->audit()->log([
            'action' => 'privilege.revoked',
            'model_type' => $this->config('database.models.role'),
            'model_id' => $roleId,
            'old_values' => ['privilege' => $privilege],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Management
    |--------------------------------------------------------------------------
    */

    /**
     * Clear all Hyro caches.
     *
     * @return void
     */
    public function clearCache(): void
    {
        // Clear role/privilege cache
        cache()->forget('hyro.roles');
        cache()->forget('hyro.privileges');
        
        // Clear sidebar cache
        cache()->forget('hyro.sidebar.groups');
        cache()->forget('hyro.active.plugins');
        
        // Clear plugin cache
        cache()->forget('hyro.plugins.list');
        cache()->forget('hyro.plugins.states');
        
        // Clear user permission cache (if exists)
        cache()->tags(['hyro.permissions'])->flush();

        // Log the action
        $this->audit()->log([
            'action' => 'cache.cleared',
            'model_type' => 'system',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Emergency Security Features
    |--------------------------------------------------------------------------
    */

    /**
     * Enable emergency lockdown mode.
     * Disables all non-admin access to the system.
     *
     * @return void
     */
    public function emergencyLockdown(): void
    {
        cache()->put('hyro.emergency.lockdown', true, now()->addHours(24));
        
        // Log the action
        $this->audit()->log([
            'action' => 'system.lockdown.enabled',
            'model_type' => 'system',
            'new_values' => [
                'enabled_at' => now()->toDateTimeString(),
                'enabled_by' => auth()->id(),
            ],
        ]);
    }

    /**
     * Disable emergency lockdown mode.
     *
     * @return void
     */
    public function emergencyUnlock(): void
    {
        cache()->forget('hyro.emergency.lockdown');
        
        // Log the action
        $this->audit()->log([
            'action' => 'system.lockdown.disabled',
            'model_type' => 'system',
            'new_values' => [
                'disabled_at' => now()->toDateTimeString(),
                'disabled_by' => auth()->id(),
            ],
        ]);
    }

    /**
     * Check if system is in emergency lockdown mode.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool) cache()->get('hyro.emergency.lockdown', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Hook System
    |--------------------------------------------------------------------------
    */

    /**
     * Register an action hook.
     *
     * @param  string  $name  Hook name (e.g., 'hyro.user.created')
     * @param  callable  $callback  Callback function
     * @param  int  $priority  Priority (lower = higher priority, default 10)
     * @param  string  $pluginId  Plugin identifier (default 'core')
     * @return void
     */
    public function addAction(string $name, callable $callback, int $priority = 10, string $pluginId = 'core'): void
    {
        $this->hookManager->addAction($name, $callback, $priority, $pluginId);
    }

    /**
     * Execute an action hook.
     *
     * @param  string  $name  Hook name
     * @param  mixed  ...$args  Arguments to pass to callbacks
     * @return void
     */
    public function doAction(string $name, mixed ...$args): void
    {
        $this->hookManager->doAction($name, ...$args);
    }

    /**
     * Register a filter hook.
     *
     * @param  string  $name  Hook name (e.g., 'hyro.seo.title')
     * @param  callable  $callback  Callback function (must return modified value)
     * @param  int  $priority  Priority (lower = higher priority, default 10)
     * @param  string  $pluginId  Plugin identifier (default 'core')
     * @return void
     */
    public function addFilter(string $name, callable $callback, int $priority = 10, string $pluginId = 'core'): void
    {
        $this->hookManager->addFilter($name, $callback, $priority, $pluginId);
    }

    /**
     * Apply filter hooks to a value.
     *
     * @param  string  $name  Hook name
     * @param  mixed  $value  Value to filter
     * @param  mixed  ...$args  Additional arguments to pass to callbacks
     * @return mixed  Filtered value
     */
    public function applyFilters(string $name, mixed $value, mixed ...$args): mixed
    {
        return $this->hookManager->applyFilters($name, $value, ...$args);
    }

    /**
     * Remove an action hook.
     *
     * @param  string  $name  Hook name
     * @param  callable|null  $callback  Specific callback to remove, or null to remove all
     * @return void
     */
    public function removeAction(string $name, ?callable $callback = null): void
    {
        $this->hookManager->removeAction($name, $callback);
    }

    /**
     * Remove a filter hook.
     *
     * @param  string  $name  Hook name
     * @param  callable|null  $callback  Specific callback to remove, or null to remove all
     * @return void
     */
    public function removeFilter(string $name, ?callable $callback = null): void
    {
        $this->hookManager->removeFilter($name, $callback);
    }

    /**
     * Remove any hook (action or filter).
     *
     * @param  string  $name  Hook name
     * @param  callable|null  $callback  Specific callback to remove, or null to remove all
     * @return void
     */
    public function removeHook(string $name, ?callable $callback = null): void
    {
        $this->hookManager->removeHook($name, $callback);
    }

    /**
     * Check if an action hook exists.
     *
     * @param  string  $name  Hook name
     * @return bool
     */
    public function hasAction(string $name): bool
    {
        return $this->hookManager->hasAction($name);
    }

    /**
     * Check if a filter hook exists.
     *
     * @param  string  $name  Hook name
     * @return bool
     */
    public function hasFilter(string $name): bool
    {
        return $this->hookManager->hasFilter($name);
    }

    /**
     * Check if any hook (action or filter) exists.
     *
     * @param  string  $name  Hook name
     * @return bool
     */
    public function hasHook(string $name): bool
    {
        return $this->hookManager->hasHook($name);
    }

    /**
     * Get all registered hooks.
     *
     * @param  string  $type  Type of hooks to get ('all', 'actions', 'filters')
     * @return array
     */
    public function getHooks(string $type = 'all'): array
    {
        return $this->hookManager->getHooks($type);
    }

    /**
     * Get the hook manager instance.
     *
     * @return \Marufsharia\Hyro\Support\Hooks\HookManager
     */
    public function hooks(): HookManager
    {
        return $this->hookManager;
    }
}


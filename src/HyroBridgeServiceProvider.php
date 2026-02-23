<?php

namespace Marufsharia\Hyro;

use Illuminate\Support\ServiceProvider;

/**
 * Hyro Bridge Service Provider
 * 
 * This service provider acts as a bridge between the monolithic package
 * and the new modular packages. It provides backward compatibility by:
 * 1. Loading all modular package service providers
 * 2. Creating class aliases for backward compatibility
 * 3. Ensuring existing code continues to work without changes
 */
class HyroBridgeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register all modular package service providers
        $this->registerModularPackages();
        
        // Create class aliases for backward compatibility
        $this->registerClassAliases();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Boot logic if needed
    }

    /**
     * Register all modular package service providers.
     */
    private function registerModularPackages(): void
    {
        // Core package
        if (class_exists(\Marufsharia\Hyro\Core\CoreServiceProvider::class)) {
            $this->app->register(\Marufsharia\Hyro\Core\CoreServiceProvider::class);
        }

        // Plugin package
        if (class_exists(\Marufsharia\Hyro\Plugin\PluginServiceProvider::class)) {
            $this->app->register(\Marufsharia\Hyro\Plugin\PluginServiceProvider::class);
        }

        // Auth package
        if (class_exists(\Marufsharia\Hyro\Auth\AuthServiceProvider::class)) {
            $this->app->register(\Marufsharia\Hyro\Auth\AuthServiceProvider::class);
        }

        // Admin Panel package
        if (class_exists(\Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider::class)) {
            $this->app->register(\Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider::class);
        }

        // CRUD package
        if (class_exists(\Marufsharia\Hyro\Crud\CrudServiceProvider::class)) {
            $this->app->register(\Marufsharia\Hyro\Crud\CrudServiceProvider::class);
        }

        // API package
        if (class_exists(\Marufsharia\Hyro\Api\ApiServiceProvider::class)) {
            $this->app->register(\Marufsharia\Hyro\Api\ApiServiceProvider::class);
        }
    }

    /**
     * Register class aliases for backward compatibility.
     * 
     * This ensures that existing code using old namespaces continues to work.
     */
    private function registerClassAliases(): void
    {
        // Core Models
        $this->aliasClass('Marufsharia\Hyro\Models\User', 'Marufsharia\Hyro\Core\Models\User');
        $this->aliasClass('Marufsharia\Hyro\Models\Role', 'Marufsharia\Hyro\Core\Models\Role');
        $this->aliasClass('Marufsharia\Hyro\Models\Privilege', 'Marufsharia\Hyro\Core\Models\Privilege');
        $this->aliasClass('Marufsharia\Hyro\Models\AuditLog', 'Marufsharia\Hyro\Core\Models\AuditLog');
        $this->aliasClass('Marufsharia\Hyro\Models\UserSuspension', 'Marufsharia\Hyro\Core\Models\UserSuspension');
        $this->aliasClass('Marufsharia\Hyro\Models\UserActivityLog', 'Marufsharia\Hyro\Core\Models\UserActivityLog');
        $this->aliasClass('Marufsharia\Hyro\Models\HyroSetting', 'Marufsharia\Hyro\Core\Models\HyroSetting');
        $this->aliasClass('Marufsharia\Hyro\Models\BaseModel', 'Marufsharia\Hyro\Core\Models\BaseModel');

        // Core Contracts
        $this->aliasClass('Marufsharia\Hyro\Contracts\HyroContract', 'Marufsharia\Hyro\Core\Contracts\HyroContract');
        $this->aliasClass('Marufsharia\Hyro\Contracts\HyroUserContract', 'Marufsharia\Hyro\Core\Contracts\HyroUserContract');
        $this->aliasClass('Marufsharia\Hyro\Contracts\AuthorizationResolverContract', 'Marufsharia\Hyro\Core\Contracts\AuthorizationResolverContract');
        $this->aliasClass('Marufsharia\Hyro\Contracts\CacheInvalidatorContract', 'Marufsharia\Hyro\Core\Contracts\CacheInvalidatorContract');

        // Core Helpers
        $this->aliasClass('Marufsharia\Hyro\Helpers\HyroAsset', 'Marufsharia\Hyro\Core\Helpers\HyroAsset');

        // Core Traits
        $this->aliasClass('Marufsharia\Hyro\Traits\HasHyroFeatures', 'Marufsharia\Hyro\Core\Traits\HasHyroFeatures');
        $this->aliasClass('Marufsharia\Hyro\Traits\HasHyroAccess', 'Marufsharia\Hyro\Core\Traits\HasHyroAccess');
        $this->aliasClass('Marufsharia\Hyro\Traits\LogsAuditEvents', 'Marufsharia\Hyro\Core\Traits\LogsAuditEvents');
        $this->aliasClass('Marufsharia\Hyro\Traits\HasProfileManagement', 'Marufsharia\Hyro\Core\Traits\HasProfileManagement');
        $this->aliasClass('Marufsharia\Hyro\Traits\ValidatesInputWithRetry', 'Marufsharia\Hyro\Core\Traits\ValidatesInputWithRetry');
        $this->aliasClass('Marufsharia\Hyro\Traits\WithAlerts', 'Marufsharia\Hyro\Core\Traits\WithAlerts');
        $this->aliasClass('Marufsharia\Hyro\Traits\FiresHooks', 'Marufsharia\Hyro\Core\Traits\FiresHooks');

        // Core Services
        $this->aliasClass('Marufsharia\Hyro\Services\AuthorizationService', 'Marufsharia\Hyro\Core\Services\AuthorizationService');
        $this->aliasClass('Marufsharia\Hyro\Services\CacheInvalidator', 'Marufsharia\Hyro\Core\Services\CacheInvalidator');
        $this->aliasClass('Marufsharia\Hyro\Services\GateRegistrar', 'Marufsharia\Hyro\Core\Services\GateRegistrar');

        // Core Repositories
        $this->aliasClass('Marufsharia\Hyro\Repositories\BaseRepository', 'Marufsharia\Hyro\Core\Repositories\BaseRepository');
        $this->aliasClass('Marufsharia\Hyro\Repositories\RoleRepository', 'Marufsharia\Hyro\Core\Repositories\RoleRepository');
        $this->aliasClass('Marufsharia\Hyro\Repositories\PrivilegeRepository', 'Marufsharia\Hyro\Core\Repositories\PrivilegeRepository');
        $this->aliasClass('Marufsharia\Hyro\Repositories\UserRepository', 'Marufsharia\Hyro\Core\Repositories\UserRepository');
        $this->aliasClass('Marufsharia\Hyro\Repositories\AuditRepository', 'Marufsharia\Hyro\Core\Repositories\AuditRepository');

        // Core Events
        $this->aliasClass('Marufsharia\Hyro\Events\RoleAssigned', 'Marufsharia\Hyro\Core\Events\RoleAssigned');
        $this->aliasClass('Marufsharia\Hyro\Events\RoleRevoked', 'Marufsharia\Hyro\Core\Events\RoleRevoked');
        $this->aliasClass('Marufsharia\Hyro\Events\RoleCreated', 'Marufsharia\Hyro\Core\Events\RoleCreated');
        $this->aliasClass('Marufsharia\Hyro\Events\RoleUpdated', 'Marufsharia\Hyro\Core\Events\RoleUpdated');
        $this->aliasClass('Marufsharia\Hyro\Events\RoleDeleted', 'Marufsharia\Hyro\Core\Events\RoleDeleted');
        $this->aliasClass('Marufsharia\Hyro\Events\PrivilegeGranted', 'Marufsharia\Hyro\Core\Events\PrivilegeGranted');
        $this->aliasClass('Marufsharia\Hyro\Events\PrivilegeRevoked', 'Marufsharia\Hyro\Core\Events\PrivilegeRevoked');
        $this->aliasClass('Marufsharia\Hyro\Events\PrivilegeCreated', 'Marufsharia\Hyro\Core\Events\PrivilegeCreated');
        $this->aliasClass('Marufsharia\Hyro\Events\PrivilegeUpdated', 'Marufsharia\Hyro\Core\Events\PrivilegeUpdated');
        $this->aliasClass('Marufsharia\Hyro\Events\UserSuspended', 'Marufsharia\Hyro\Core\Events\UserSuspended');
        $this->aliasClass('Marufsharia\Hyro\Events\UserUnsuspended', 'Marufsharia\Hyro\Core\Events\UserUnsuspended');

        // Core Facades
        $this->aliasClass('Marufsharia\Hyro\Facades\Hyro', 'Marufsharia\Hyro\Core\Facades\Hyro');
        $this->aliasClass('Marufsharia\Hyro\Facades\Plugin', 'Marufsharia\Hyro\Core\Facades\Plugin');

        // Core Console
        $this->aliasClass('Marufsharia\Hyro\Console\Commands\BaseCommand', 'Marufsharia\Hyro\Core\Console\Commands\BaseCommand');
        $this->aliasClass('Marufsharia\Hyro\Console\Concerns\Confirmable', 'Marufsharia\Hyro\Core\Console\Concerns\Confirmable');
        $this->aliasClass('Marufsharia\Hyro\Console\Concerns\Validatable', 'Marufsharia\Hyro\Core\Console\Concerns\Validatable');

        // Plugin System
        $this->aliasClass('Marufsharia\Hyro\Support\Plugins\PluginManager', 'Marufsharia\Hyro\Plugin\Support\Plugins\PluginManager');
        $this->aliasClass('Marufsharia\Hyro\Support\Plugins\HyroPlugin', 'Marufsharia\Hyro\Plugin\Support\Plugins\HyroPlugin');
        $this->aliasClass('Marufsharia\Hyro\Support\Hooks\HookManager', 'Marufsharia\Hyro\Plugin\Support\Hooks\HookManager');

        // Auth Services
        $this->aliasClass('Marufsharia\Hyro\Services\TokenSynchronizationService', 'Marufsharia\Hyro\Auth\Services\TokenSynchronizationService');
        $this->aliasClass('Marufsharia\Hyro\Listeners\TokenSynchronizationListener', 'Marufsharia\Hyro\Auth\Listeners\TokenSynchronizationListener');

        // Auth Middleware
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\Authenticate', 'Marufsharia\Hyro\Auth\Http\Middleware\Authenticate');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\RedirectIfAuthenticated', 'Marufsharia\Hyro\Auth\Http\Middleware\RedirectIfAuthenticated');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\RequireTwoFactorAuth', 'Marufsharia\Hyro\Auth\Http\Middleware\RequireTwoFactorAuth');

        // Core Middleware
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\EnsureHasAbility', 'Marufsharia\Hyro\Core\Http\Middleware\EnsureHasAbility');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\EnsureHasAnyPrivilege', 'Marufsharia\Hyro\Core\Http\Middleware\EnsureHasAnyPrivilege');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\EnsureHasAnyRole', 'Marufsharia\Hyro\Core\Http\Middleware\EnsureHasAnyRole');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\EnsureHasPrivilege', 'Marufsharia\Hyro\Core\Http\Middleware\EnsureHasPrivilege');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\EnsureHasRole', 'Marufsharia\Hyro\Core\Http\Middleware\EnsureHasRole');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\AuditRequest', 'Marufsharia\Hyro\Core\Http\Middleware\AuditRequest');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\HyroMiddleware', 'Marufsharia\Hyro\Core\Http\Middleware\HyroMiddleware');
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\HyroPrivilegeMiddleware', 'Marufsharia\Hyro\Core\Http\Middleware\HyroPrivilegeMiddleware');

        // API Middleware
        $this->aliasClass('Marufsharia\Hyro\Http\Middleware\EnsureApiEnabled', 'Marufsharia\Hyro\Api\Http\Middleware\EnsureApiEnabled');

        // Admin Panel Controllers
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\DashboardController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\DashboardController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\PrivilegeController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\PrivilegeController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\RoleController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\RoleController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\SettingsController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\SettingsController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\UserRoleController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\UserRoleController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\Auth\AuthController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\Auth\AuthController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\Auth\ForgotPasswordController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\Auth\ForgotPasswordController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\Auth\RegisterController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\Auth\RegisterController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\Auth\ResetPasswordController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\Auth\ResetPasswordController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Admin\Auth\TwoFactorController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\Auth\TwoFactorController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\NotificationController', 'Marufsharia\Hyro\AdminPanel\Http\Controllers\NotificationController');

        // Auth Controllers
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\AuthController', 'Marufsharia\Hyro\Auth\Http\Controllers\AuthController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\ForgotPasswordController', 'Marufsharia\Hyro\Auth\Http\Controllers\ForgotPasswordController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\RegisterController', 'Marufsharia\Hyro\Auth\Http\Controllers\RegisterController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\ResetPasswordController', 'Marufsharia\Hyro\Auth\Http\Controllers\ResetPasswordController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\TwoFactorController', 'Marufsharia\Hyro\Auth\Http\Controllers\TwoFactorController');

        // API Controllers
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\AuthController', 'Marufsharia\Hyro\Api\Http\Controllers\AuthController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\BaseController', 'Marufsharia\Hyro\Api\Http\Controllers\BaseController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\BaseCrudController', 'Marufsharia\Hyro\Api\Http\Controllers\BaseCrudController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\PrivilegeController', 'Marufsharia\Hyro\Api\Http\Controllers\PrivilegeController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\RoleController', 'Marufsharia\Hyro\Api\Http\Controllers\RoleController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\SuspensionController', 'Marufsharia\Hyro\Api\Http\Controllers\SuspensionController');
        $this->aliasClass('Marufsharia\Hyro\Http\Controllers\Api\UserController', 'Marufsharia\Hyro\Api\Http\Controllers\UserController');

        // Admin Panel Services
        $this->aliasClass('Marufsharia\Hyro\Services\SettingsService', 'Marufsharia\Hyro\AdminPanel\Services\SettingsService');
        $this->aliasClass('Marufsharia\Hyro\Services\SidebarRegistry', 'Marufsharia\Hyro\AdminPanel\Services\SidebarRegistry');
        $this->aliasClass('Marufsharia\Hyro\Blade\HyroBladeHelper', 'Marufsharia\Hyro\AdminPanel\Blade\HyroBladeHelper');

        // CRUD Services
        $this->aliasClass('Marufsharia\Hyro\Services\SmartCrudRouteManager', 'Marufsharia\Hyro\Crud\Services\SmartCrudRouteManager');
        $this->aliasClass('Marufsharia\Hyro\Services\PermissionGenerator', 'Marufsharia\Hyro\Crud\Services\PermissionGenerator');
        $this->aliasClass('Marufsharia\Hyro\Services\CrudRouteAutoDiscoverer', 'Marufsharia\Hyro\Crud\Services\CrudRouteAutoDiscoverer');
        $this->aliasClass('Marufsharia\Hyro\Livewire\BaseCrudComponent', 'Marufsharia\Hyro\Crud\Livewire\BaseCrudComponent');

        // API Resources
        $this->aliasClass('Marufsharia\Hyro\Http\Resources\UserResource', 'Marufsharia\Hyro\Api\Http\Resources\UserResource');
        $this->aliasClass('Marufsharia\Hyro\Http\Resources\TokenResource', 'Marufsharia\Hyro\Api\Http\Resources\TokenResource');
    }

    /**
     * Create a class alias if the target class exists.
     */
    private function aliasClass(string $alias, string $original): void
    {
        if (class_exists($original) && !class_exists($alias, false)) {
            class_alias($original, $alias);
        }
    }
}

<?php

namespace MarufSharia\Hyro;

use Illuminate\Support\Manager;
use Illuminate\Support\Facades\Schema;
use Marufsharia\Hyro\Drivers\SanctumDriver;

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
}

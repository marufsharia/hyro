<?php

namespace Marufsharia\Hyro\AdminPanel\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Marufsharia\Hyro\Core\Models\HyroSetting;

class SettingsService
{
    /**
     * Get all settings grouped by section.
     */
    public function getAllSettings(): array
    {
        return [
            'general' => $this->getGeneralSettings(),
            'admin' => $this->getAdminSettings(),
            'crud' => $this->getCrudSettings(),
            'rbac' => $this->getRbacSettings(),
            'plugins' => $this->getPluginSettings(),
            'system' => $this->getSystemSettings(),
        ];
    }

    /**
     * Get general settings.
     */
    public function getGeneralSettings(): array
    {
        return [
            'locale' => hyro_config('locale', 'en'),
            'timezone' => hyro_config('timezone', 'UTC'),
            'maintenance_mode' => hyro_config('maintenance_mode', false),
        ];
    }

    /**
     * Get admin settings.
     */
    public function getAdminSettings(): array
    {
        return [
            'admin_prefix' => hyro_config('admin.route.prefix', 'admin/hyro'),
            'pagination_limit' => hyro_config('admin.pagination.per_page', 15),
            'layout' => hyro_config('admin.layout', 'hyro::admin.layouts.app'),
        ];
    }

    /**
     * Get CRUD settings.
     */
    public function getCrudSettings(): array
    {
        return [
            'generate_api' => hyro_config('crud.generate_api', true),
            'soft_delete' => hyro_config('crud.soft_delete', true),
            'auto_permission' => hyro_config('crud.auto_permission', true),
        ];
    }

    /**
     * Get RBAC settings.
     */
    public function getRbacSettings(): array
    {
        return [
            'enabled' => hyro_config('rbac.enabled', true),
            'default_role' => hyro_config('rbac.default_role', 'user'),
            'super_admin_role' => hyro_config('rbac.super_admin_role', 'super-admin'),
            'cache_permissions' => hyro_config('rbac.cache_permissions', true),
        ];
    }

    /**
     * Get plugin settings.
     */
    public function getPluginSettings(): array
    {
        return [
            'auto_load' => hyro_config('plugins.autoload', true),
            'allow_disable' => hyro_config('plugins.allow_disable', true),
        ];
    }

    /**
     * Get system settings.
     */
    public function getSystemSettings(): array
    {
        return [
            'cache_enabled' => hyro_config('cache.enabled', true),
            'cache_ttl' => hyro_config('cache.ttl.roles', 3600),
            'auditing_enabled' => hyro_config('auditing.enabled', true),
        ];
    }

    /**
     * Update settings for a section.
     */
    public function updateSettings(string $section, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $fullKey = $this->getFullKey($section, $key);
            hyro_set_config($fullKey, $value);
        }

        // Clear relevant caches
        $this->clearCaches();
    }

    /**
     * Get full configuration key from section and key.
     */
    protected function getFullKey(string $section, string $key): string
    {
        $keyMap = [
            'general' => [
                'locale' => 'locale',
                'timezone' => 'timezone',
                'maintenance_mode' => 'maintenance_mode',
            ],
            'admin' => [
                'admin_prefix' => 'admin.route.prefix',
                'pagination_limit' => 'admin.pagination.per_page',
                'layout' => 'admin.layout',
            ],
            'crud' => [
                'generate_api' => 'crud.generate_api',
                'soft_delete' => 'crud.soft_delete',
                'auto_permission' => 'crud.auto_permission',
            ],
            'rbac' => [
                'enabled' => 'rbac.enabled',
                'default_role' => 'rbac.default_role',
                'super_admin_role' => 'rbac.super_admin_role',
                'cache_permissions' => 'rbac.cache_permissions',
            ],
            'plugins' => [
                'auto_load' => 'plugins.autoload',
                'allow_disable' => 'plugins.allow_disable',
            ],
            'system' => [
                'cache_enabled' => 'cache.enabled',
                'cache_ttl' => 'cache.ttl.roles',
                'auditing_enabled' => 'auditing.enabled',
            ],
        ];

        return $keyMap[$section][$key] ?? "{$section}.{$key}";
    }

    /**
     * Clear all relevant caches.
     */
    public function clearCaches(): void
    {
        // Clear Hyro settings cache
        HyroSetting::clearCache();

        // Clear Laravel caches
        Cache::flush();
    }

    /**
     * Clear config cache.
     */
    public function clearConfigCache(): void
    {
        Artisan::call('config:clear');
    }

    /**
     * Clear route cache.
     */
    public function clearRouteCache(): void
    {
        Artisan::call('route:clear');
    }

    /**
     * Clear application cache.
     */
    public function clearAppCache(): void
    {
        Artisan::call('cache:clear');
    }

    /**
     * Clear all caches (config, route, app).
     */
    public function clearAllCaches(): void
    {
        $this->clearConfigCache();
        $this->clearRouteCache();
        $this->clearAppCache();
        $this->clearCaches();
    }
}

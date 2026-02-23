<?php

use Marufsharia\Hyro\Core\Models\HyroSetting;

if (!function_exists('hyro_config')) {
    /**
     * Get Hyro configuration value with DB override support.
     * 
     * Priority:
     * 1. Database setting (if exists)
     * 2. Config file value
     * 3. Default value
     *
     * @param string $key Configuration key (dot notation)
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function hyro_config(string $key, $default = null)
    {
        $cacheKey = "hyro_setting_{$key}";

        // Try to get from cache first
        $dbValue = cache()->remember($cacheKey, 3600, function () use ($key) {
            try {
                return HyroSetting::getValue($key);
            } catch (\Exception $e) {
                // If table doesn't exist yet (during migration), return null
                return null;
            }
        });

        // If DB value exists, return it
        if (!is_null($dbValue)) {
            return $dbValue;
        }

        // Fallback to config file
        return config("hyro.{$key}", $default);
    }
}

if (!function_exists('hyro_set_config')) {
    /**
     * Set Hyro configuration value in database.
     *
     * @param string $key Configuration key
     * @param mixed $value Value to set
     * @return void
     */
    function hyro_set_config(string $key, $value): void
    {
        HyroSetting::setValue($key, $value);
    }
}

if (!function_exists('hyro_clear_config_cache')) {
    /**
     * Clear all Hyro configuration cache.
     *
     * @return void
     */
    function hyro_clear_config_cache(): void
    {
        HyroSetting::clearCache();
    }
}

if (!function_exists('hyro_menu_icon')) {
    /**
     * Get custom menu icon or return default.
     *
     * @param string $menuItem Menu item key (dashboard, roles, privileges, etc.)
     * @param string $defaultIcon Default SVG path if no custom icon
     * @return string SVG path
     */
    function hyro_menu_icon(string $menuItem, string $defaultIcon): string
    {
        // Check if Menu Icon Customizer plugin is active
        if (class_exists('\Codebusket\MenuIconCustomizer\Plugin')) {
            try {
                $pluginManager = app(\Marufsharia\Hyro\Support\Plugins\PluginManager::class);
                
                // Use the plugin ID (menu-icon-customizer) instead of class name
                if (!$pluginManager->isPluginActive('menu-icon-customizer')) {
                    return $defaultIcon;
                }
            } catch (\Exception $e) {
                // If PluginManager doesn't exist or fails, return default
                return $defaultIcon;
            }
        } else {
            // Plugin not installed, return default
            return $defaultIcon;
        }
        
        // Plugin is active, check for custom icon
        $customIcon = hyro_config("appearance.menu_icons.{$menuItem}");
        return $customIcon ?: $defaultIcon;
    }
}


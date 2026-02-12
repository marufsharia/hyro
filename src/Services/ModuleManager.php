<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ModuleManager
{
    /**
     * Module registry file location
     */
    protected static string $path = "storage/hyro/modules.json";

    /**
     * Get full JSON file path
     */
    protected static function filePath(): string
    {
        return base_path(self::$path);
    }

    /**
     * Load all modules from registry
     */
    public static function all(): array
    {
        $file = self::filePath();

        if (!File::exists($file)) {
            return [];
        }

        return json_decode(File::get($file), true) ?? [];
    }

    /**
     * Save module registry back into file
     */
    protected static function save(array $modules): void
    {
        $file = self::filePath();

        File::ensureDirectoryExists(dirname($file));

        File::put($file, json_encode($modules, JSON_PRETTY_PRINT));
    }

    /**
     * Register or update a module
     */
    public static function register(string $key, array $module): void
    {
        $modules = self::all();

        $module["enabled"] = $module["enabled"] ?? true;

        $modules[$key] = $module;

        self::save($modules);
    }

    /**
     * Get a single module
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
    /**
     * Get modules organized for sidebar
     */
    public static function getSidebarGroups(): array
    {
        $modules = self::enabled();

        $groups = [];

        foreach ($modules as $slug => $module) {
            $group = $module['group'] ?? 'Modules';
            $groups[$group]['group'] = $group;
            $groups[$group]['items'][] = [
                'title' => $module['title'],
                'route' => 'admin.' . \Str::plural($slug),
                'icon'  => $module['icon'] ?? 'folder',
            ];
        }

        // Add plugins section
        $plugins = self::getActivePlugins();
        if (!empty($plugins)) {
            $groups['Plugins']['group'] = 'Plugins';
            $groups['Plugins']['items'] = $plugins;
        }

        return $groups;
    }

    /**
     * Get active plugins for sidebar
     */
    public static function getActivePlugins(): array
    {
        $pluginManager = app('hyro.plugins');
        $states = $pluginManager->getPluginStates();
        $allPlugins = $pluginManager->getAllPlugins();
        
        $activePlugins = [];
        
        foreach ($states as $pluginId => $state) {
            if (($state['active'] ?? false) && isset($allPlugins[$pluginId])) {
                $plugin = $allPlugins[$pluginId];
                $activePlugins[] = [
                    'title' => $plugin['meta']['name'] ?? ucfirst($pluginId),
                    'route' => 'hyro.plugin.' . $pluginId . '.index',
                    'icon'  => 'puzzle',
                    'url'   => '/hyro/plugins/' . $pluginId,
                ];
            }
        }
        
        return $activePlugins;
    }
    /**
     * Check if module exists
     */
    public static function exists(string $key): bool
    {
        return isset(self::all()[$key]);
    }

    /**
     * Return only enabled modules
     */
    public static function enabled(): array
    {
        return collect(self::all())
            ->filter(fn($m) => ($m["enabled"] ?? true) === true)
            ->toArray();
    }

    /**
     * Enable a module
     */
    public static function enable(string $key): bool
    {
        $modules = self::all();

        if (!isset($modules[$key])) {
            return false;
        }

        $modules[$key]["enabled"] = true;

        self::save($modules);

        return true;
    }

    /**
     * Disable a module (hide sidebar + disable routes)
     */
    public static function disable(string $key): bool
    {
        $modules = self::all();

        if (!isset($modules[$key])) {
            return false;
        }

        $modules[$key]["enabled"] = false;

        self::save($modules);

        return true;
    }

    /**
     * Unregister module (remove from registry only)
     */
    public static function unregister(string $key): bool
    {
        $modules = self::all();

        if (!isset($modules[$key])) {
            return false;
        }

        unset($modules[$key]);

        self::save($modules);

        return true;
    }

    /**
     * Delete module fully:
     * - delete files
     * - rollback migrations
     * - remove permission from DB
     * - unregister module
     */
    public static function delete(string $key, bool $rollback = false): bool
    {
        $modules = self::all();

        if (!isset($modules[$key])) {
            return false;
        }

        $module = $modules[$key];

        /**
         * 1. Rollback migration if enabled
         */
        if ($rollback && !empty($module["paths"]["migration"])) {

            $migrationFile = base_path($module["paths"]["migration"]);

            if (File::exists($migrationFile)) {
                Artisan::call("migrate:rollback", [
                    "--path" => $module["paths"]["migration"],
                    "--force" => true,
                ]);
            }
        }

        /**
         * 2. Delete all module files safely
         */
        if (!empty($module["paths"])) {

            foreach ($module["paths"] as $type => $path) {

                if (!$path) continue;

                $fullPath = base_path($path);

                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }

        /**
         * 3. Remove module permission from DB
         */
        if (!empty($module["permission"])) {

            DB::table("privileges")
                ->where("name", $module["permission"])
                ->delete();
        }

        /**
         * 4. Remove module from registry
         */
        unset($modules[$key]);

        self::save($modules);

        return true;
    }
}

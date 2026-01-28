<?php

namespace Marufsharia\Hyro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void discover()
 * @method static void load()
 * @method static void loadPlugin(string $id)
 * @method static void install(string $id)
 * @method static void uninstall(string $id)
 * @method static void activate(string $id)
 * @method static void deactivate(string $id)
 * @method static void refresh()
 * @method static void addHook(string $name, callable $callback, int $priority = 10)
 * @method static mixed executeHook(string $name, mixed ...$args)
 * @method static \Illuminate\Support\Collection getLoadedPlugins()
 * @method static \Illuminate\Support\Collection getAllPlugins()
 * @method static bool isLoaded(string $id)
 * @method static \Marufsharia\Hyro\Support\Plugins\HyroPlugin|null getPlugin(string $id)
 *
 * @see \Marufsharia\Hyro\Support\Plugins\PluginManager
 */
class Plugin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hyro.plugins';
    }
}

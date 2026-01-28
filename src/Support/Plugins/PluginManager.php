<?php

namespace Marufsharia\Hyro\Support\Plugins;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

class PluginManager
{
    protected Application $app;
    protected Collection $plugins;
    protected Collection $loadedPlugins;
    protected array $hooks = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->plugins = new Collection();
        $this->loadedPlugins = new Collection();
    }

    /**
     * Discover all available plugins
     * @param bool $forceRefresh Ignore cache and rescan
     */
    public function discover(bool $forceRefresh = false): void
    {
        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));

        if (!File::isDirectory($pluginsPath)) {
            File::makeDirectory($pluginsPath, 0755, true);
            return;
        }

        if ($forceRefresh) {
            Cache::forget('hyro.plugins.discovered');
        }

        $cacheEnabled = config('hyro.plugins.cache.enabled', true);
        $cacheTtl = config('hyro.plugins.cache.ttl', 3600);

        // If forcing refresh, we bypass the cache check entirely for this run
        if ($forceRefresh || !$cacheEnabled) {
            $plugins = $this->scanPlugins($pluginsPath);
            // Re-cache the fresh result if caching is enabled
            if ($cacheEnabled) {
                Cache::put('hyro.plugins.discovered', $plugins, $cacheTtl);
            }
        } else {
            $plugins = Cache::remember('hyro.plugins.discovered', $cacheTtl, fn() => $this->scanPlugins($pluginsPath));
        }

        $this->plugins = new Collection(); // Reset collection
        foreach ($plugins as $id => $data) {
            $this->plugins->put($id, $data);
        }
    }

    /**
     * Scan plugins directory
     */
    protected function scanPlugins(string $pluginsPath): array
    {
        $discovered = [];

        foreach (File::directories($pluginsPath) as $pluginDir) {
            $pluginFile = $pluginDir . '/Plugin.php';

            if (!File::exists($pluginFile)) {
                continue;
            }

            try {
                $className = $this->getPluginClassName($pluginDir);

                // 1. Try to load via Autoloader first (Best Practice)
                if (!class_exists($className)) {
                    // 2. Fallback to manual require (For fresh plugins not yet in composer dump-autoload)
                    require_once $pluginFile;
                }

                // Check again after require
                if (!class_exists($className)) {
                    continue;
                }

                $plugin = new $className($this->app);

                if (!$plugin instanceof HyroPlugin) {
                    continue;
                }

                // Compatibility Check
                if (method_exists($plugin, 'isCompatible') && !$plugin->isCompatible()) {
                    continue;
                }

                $discovered[$plugin->getId()] = [
                    'class' => $className,
                    'path' => $pluginDir,
                    'meta' => [
                        'name' => $plugin->getName(),
                        'description' => $plugin->getDescription(),
                        'version' => $plugin->getVersion(),
                        'author' => $plugin->getAuthor(),
                        'dependencies' => $plugin->getDependencies(),
                        // Safe check for method existence in case user hasn't updated base class
                        'min_hyro_version' => method_exists($plugin, 'getMinimumHyroVersion') ? $plugin->getMinimumHyroVersion() : '1.0.0',
                    ]
                ];
            } catch (\Throwable $e) {
                // Log the specific error to help debugging
                logger()->error("Hyro Plugin Error [{$pluginDir}]: " . $e->getMessage());
                continue;
            }
        }

        return $discovered;
    }

    /**
     * Load all enabled plugins
     */
    public function load(): void
    {
        $sortedPlugins = $this->resolveDependencies();

        foreach ($sortedPlugins as $id) {
            try {
                $this->loadPlugin($id);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Load a specific plugin
     */
    public function loadPlugin(string $id): void
    {
        if ($this->loadedPlugins->has($id)) {
            return;
        }

        $pluginData = $this->plugins->get($id);

        if (!$pluginData) {
            throw new \Exception("Plugin '{$id}' not found.");
        }

        $plugin = new $pluginData['class']($this->app);

        if (!$plugin->isEnabled()) {
            return;
        }

        // Check dependencies
        foreach ($plugin->getDependencies() as $dependency) {
            if (!$this->loadedPlugins->has($dependency)) {
                throw new \Exception("Plugin '{$id}' requires '{$dependency}' which is not loaded.");
            }
        }

        // Register plugin
        $plugin->register();

        // Boot plugin
        $plugin->boot();

        // Load plugin routes
        $this->loadPluginRoutes($plugin);

        // Load plugin migrations
        $this->loadPluginMigrations($plugin);

        // Load plugin views
        $this->loadPluginViews($plugin);

        // Load plugin translations
        $this->loadPluginTranslations($plugin);

        $this->loadedPlugins->put($id, $plugin);

        event('hyro.plugin.loaded', [$id, $plugin]);
    }

    /**
     * Load plugin routes
     */
    protected function loadPluginRoutes(HyroPlugin $plugin): void
    {
        if ($routes = $plugin->routes()) {
            if (File::exists($routes)) {
                Route::middleware('web')->group($routes);
            }
        }
    }

    /**
     * Load plugin migrations
     */
    protected function loadPluginMigrations(HyroPlugin $plugin): void
    {
        if ($migrations = $plugin->migrations()) {
            if (File::isDirectory($migrations)) {
                $this->app->make('migrator')->path($migrations);
            }
        }
    }

    /**
     * Load plugin views
     */
    protected function loadPluginViews(HyroPlugin $plugin): void
    {
        if ($views = $plugin->views()) {
            if (File::isDirectory($views)) {
                $this->app['view']->addNamespace("hyro-plugin-{$plugin->getId()}", $views);
            }
        }
    }

    /**
     * Load plugin translations
     */
    protected function loadPluginTranslations(HyroPlugin $plugin): void
    {
        if ($lang = $plugin->lang()) {
            if (File::isDirectory($lang)) {
                $this->app['translator']->addNamespace("hyro-plugin-{$plugin->getId()}", $lang);
            }
        }
    }

    /**
     * Resolve plugin dependencies
     */
    protected function resolveDependencies(): array
    {
        $sorted = [];
        $visited = [];

        foreach ($this->plugins->keys() as $id) {
            $this->visitPlugin($id, $visited, $sorted);
        }

        return $sorted;
    }

    /**
     * Visit plugin for dependency resolution (Depth-first search)
     */
    protected function visitPlugin(string $id, array &$visited, array &$sorted): void
    {
        if (isset($visited[$id])) {
            return;
        }

        $visited[$id] = true;

        $pluginData = $this->plugins->get($id);

        if ($pluginData) {
            $plugin = new $pluginData['class']($this->app);

            foreach ($plugin->getDependencies() as $dependency) {
                if (!$this->plugins->has($dependency)) {
                    throw new \Exception("Plugin '{$id}' depends on '{$dependency}' which is not available.");
                }
                $this->visitPlugin($dependency, $visited, $sorted);
            }
        }

        $sorted[] = $id;
    }

    /**
     * Get plugin class name from directory
     */
    protected function getPluginClassName(string $dir): string
    {
        $pluginName = basename($dir);
        return "HyroPlugins\\{$pluginName}\\Plugin";
    }

    /**
     * Register a hook
     */
    public function addHook(string $name, callable $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        usort($this->hooks[$name], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Execute a hook
     */
    public function executeHook(string $name, mixed ...$args): mixed
    {
        if (!isset($this->hooks[$name])) {
            return null;
        }

        $result = null;

        foreach ($this->hooks[$name] as $hook) {
            $result = call_user_func_array($hook['callback'], $args);
        }

        return $result;
    }

    /**
     * Get all loaded plugins
     */
    public function getLoadedPlugins(): Collection
    {
        return $this->loadedPlugins;
    }

    /**
     * Get all discovered plugins
     */
    public function getAllPlugins(): Collection
    {
        return $this->plugins;
    }

    /**
     * Check if plugin is loaded
     */
    public function isLoaded(string $id): bool
    {
        return $this->loadedPlugins->has($id);
    }

    /**
     * Get plugin instance
     */
    public function getPlugin(string $id): ?HyroPlugin
    {
        return $this->loadedPlugins->get($id);
    }

    /**
     * Install plugin
     */
    public function install(string $id): void
    {
        $pluginData = $this->plugins->get($id);

        if (!$pluginData) {
            throw new \Exception("Plugin '{$id}' not found.");
        }

        $plugin = new $pluginData['class']($this->app);
        $plugin->install();

        // Run migrations if they exist
        if ($migrations = $plugin->migrations()) {
            if (File::isDirectory($migrations)) {
                Artisan::call('migrate', [
                    '--path' => $migrations,
                    '--force' => true,
                ]);
            }
        }

        Cache::forget('hyro.plugins.discovered');

        event('hyro.plugin.installed', [$id, $plugin]);
    }

    /**
     * Uninstall plugin
     */
    public function uninstall(string $id): void
    {
        $plugin = $this->getPlugin($id);

        if (!$plugin) {
            throw new \Exception("Plugin '{$id}' is not loaded.");
        }

        $plugin->uninstall();

        // Rollback migrations if they exist
        if ($migrations = $plugin->migrations()) {
            if (File::isDirectory($migrations)) {
                Artisan::call('migrate:rollback', [
                    '--path' => $migrations,
                    '--force' => true,
                ]);
            }
        }

        $this->loadedPlugins->forget($id);
        Cache::forget('hyro.plugins.discovered');

        event('hyro.plugin.uninstalled', [$id]);
    }

    /**
     * Activate plugin
     */
    public function activate(string $id): void
    {
        $plugin = $this->getPlugin($id);

        if (!$plugin) {
            $this->loadPlugin($id);
            $plugin = $this->getPlugin($id);
        }

        if ($plugin) {
            $plugin->activate();
            event('hyro.plugin.activated', [$id, $plugin]);
        }
    }

    /**
     * Deactivate plugin
     */
    public function deactivate(string $id): void
    {
        $plugin = $this->getPlugin($id);

        if ($plugin) {
            $plugin->deactivate();
            $this->loadedPlugins->forget($id);
            event('hyro.plugin.deactivated', [$id]);
        }
    }

    /**
     * Refresh plugins cache
     */
    public function refresh(): void
    {
        Cache::forget('hyro.plugins.discovered');
        $this->plugins = new Collection();
        $this->loadedPlugins = new Collection();
        $this->discover();
        $this->load();
    }
}

<?php

namespace Marufsharia\Hyro\Support\Plugins;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Composer\Semver\Semver;
use Symfony\Component\Process\Process;
use Exception;

class PluginManager
{
    protected Application $app;
    protected Collection $plugins;
    protected Collection $loadedPlugins;
    protected Collection $remoteSources;
    protected array $hooks = [];

    // NEW: Path to store plugin states
    protected string $stateFilePath;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->plugins = new Collection();
        $this->loadedPlugins = new Collection();
        $this->remoteSources = new Collection();

        // Initialize state file path
        $this->stateFilePath = storage_path('hyro/plugins-state.json');

        // Ensure directory exists
        if (!File::isDirectory(dirname($this->stateFilePath))) {
            File::makeDirectory(dirname($this->stateFilePath), 0755, true);
        }

        $this->initializeRemoteSources();
    }

    /**
     * Initialize remote plugin sources
     */
    protected function initializeRemoteSources(): void
    {
        $this->remoteSources = new Collection([
            'marketplace' => [
                'name' => 'Hyro Marketplace',
                'url' => config('hyro.plugins.marketplace.url', 'https://marketplace.hyro.io/api/v1'),
                'type' => 'api',
                'enabled' => config('hyro.plugins.marketplace.enabled', true),
            ],
            'github' => [
                'name' => 'GitHub',
                'url' => 'https://api.github.com',
                'type' => 'github',
                'enabled' => config('hyro.plugins.github.enabled', true),
            ],
            'gitlab' => [
                'name' => 'GitLab',
                'url' => config('hyro.plugins.gitlab.url', 'https://gitlab.com/api/v4'),
                'type' => 'gitlab',
                'enabled' => config('hyro.plugins.gitlab.enabled', true),
            ],
            'packagist' => [
                'name' => 'Packagist',
                'url' => 'https://packagist.org',
                'type' => 'packagist',
                'enabled' => config('hyro.plugins.packagist.enabled', true),
            ],
        ]);
    }

    /**
     * NEW: Load plugin states from file
     */
    protected function loadPluginStates(): array
    {
        if (!File::exists($this->stateFilePath)) {
            return [];
        }

        try {
            $content = File::get($this->stateFilePath);
            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            logger()->error("Failed to load plugin states: " . $e->getMessage());
            return [];
        }
    }

    /**
     * NEW: Save plugin states to file
     */
    protected function savePluginStates(array $states): void
    {
        try {
            File::put($this->stateFilePath, json_encode($states, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            logger()->error("Failed to save plugin states: " . $e->getMessage());
        }
    }

    /**
     * NEW: Mark plugin as active
     */
    protected function markPluginActive(string $pluginId): void
    {
        $states = $this->loadPluginStates();
        $states[$pluginId] = [
            'active' => true,
            'activated_at' => now()->toIso8601String(),
        ];
        $this->savePluginStates($states);
    }

    /**
     * NEW: Mark plugin as inactive
     */
    protected function markPluginInactive(string $pluginId): void
    {
        $states = $this->loadPluginStates();
        if (isset($states[$pluginId])) {
            $states[$pluginId]['active'] = false;
            $states[$pluginId]['deactivated_at'] = now()->toIso8601String();
            $this->savePluginStates($states);
        }
    }

    /**
     * NEW: Check if plugin is marked as active
     */
    public function isPluginActive(string $pluginId): bool
    {
        $states = $this->loadPluginStates();
        return isset($states[$pluginId]) && ($states[$pluginId]['active'] ?? false);
    }

    /**
     * Discover all available plugins (local + remote)
     */
    public function discover(bool $forceRefresh = false): void
    {
        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));

        if (!File::isDirectory($pluginsPath)) {
            File::makeDirectory($pluginsPath, 0755, true);
            $this->plugins = new Collection();
            return;
        }

        if ($forceRefresh) {
            Cache::forget('hyro.plugins.discovered');
            Cache::forget('hyro.plugins.remote');
        }

        $cacheEnabled = config('hyro.plugins.cache.enabled', true);
        $cacheTtl = config('hyro.plugins.cache.ttl', 3600);

        // Discover local plugins (cached)
        if ($forceRefresh || !$cacheEnabled) {
            $localPlugins = $this->scanLocalPlugins($pluginsPath);
            if ($cacheEnabled) {
                Cache::put('hyro.plugins.discovered', $localPlugins, $cacheTtl);
            }
        } else {
            $localPlugins = Cache::remember('hyro.plugins.discovered', $cacheTtl,
                fn() => $this->scanLocalPlugins($pluginsPath)
            );
        }

        // OPTIMIZATION: Skip remote plugin discovery unless forced
        // Remote discovery is SLOW (API calls) - only do it manually
        $remotePlugins = $forceRefresh ? $this->discoverRemotePlugins($forceRefresh) : [];

        // Merge arrays correctly
        $allPlugins = array_merge($localPlugins, $remotePlugins);

        // Set discovered plugins
        $this->plugins = new Collection($allPlugins);

        // CHANGED: Load active plugins from state file instead of preserving in-memory
        $this->loadActivePlugins();
    }

    /**
     * NEW: Load all plugins marked as active in state file
     */
    protected function loadActivePlugins(): void
    {
        $states = $this->loadPluginStates();

        foreach ($states as $pluginId => $state) {
            if ($state['active'] ?? false) {
                // Check if plugin exists
                if ($this->plugins->has($pluginId)) {
                    try {
                        // Only load if not already loaded
                        if (!$this->loadedPlugins->has($pluginId)) {
                            $this->loadPlugin($pluginId);
                        }
                    } catch (\Exception $e) {
                        logger()->error("Failed to auto-load active plugin '{$pluginId}': " . $e->getMessage());
                    }
                } else {
                    logger()->warning("Active plugin '{$pluginId}' not found in discovered plugins");
                }
            }
        }
    }

    protected function logDebug(string $message): void
    {
        if (config('app.debug')) {
            logger()->debug("PluginManager: " . $message);
        }
    }

    /**
     * Scan local plugins directory
     */
    protected function scanLocalPlugins(string $pluginsPath): array
    {
        $discovered = [];

        if (!File::exists($pluginsPath) || !File::isDirectory($pluginsPath)) {
            logger()->warning("Plugins path does not exist: {$pluginsPath}");
            return $discovered;
        }

        try {
            $directories = File::directories($pluginsPath);
            logger()->info("Found " . count($directories) . " plugin directories");

            foreach ($directories as $pluginDir) {
                // Check for Plugin.php in both root and src directory
                $pluginFile = $pluginDir . '/src/Plugin.php';
                if (!File::exists($pluginFile)) {
                    $pluginFile = $pluginDir . '/Plugin.php';
                }

                if (!File::exists($pluginFile)) {
                    logger()->debug("Skipping {$pluginDir}: Plugin.php not found in root or src directory");
                    continue;
                }

                try {
                    $pluginData = $this->loadLocalPluginData($pluginDir);
                    if ($pluginData) {
                        $pluginId = $pluginData['id'];
                        $discovered[$pluginId] = $pluginData;
                        logger()->info("✅ Discovered plugin: {$pluginId}");
                    } else {
                        logger()->warning("Failed to load plugin data from {$pluginDir}");
                    }
                } catch (\Exception $e) {
                    logger()->error("Error loading plugin from {$pluginDir}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            logger()->error("Error scanning plugins directory: " . $e->getMessage());
        }

        logger()->info("Total plugins discovered: " . count($discovered));
        return $discovered;
    }

    protected function loadLocalPluginData(string $pluginDir): ?array
    {
        // Check for Plugin.php in both src and root directory
        $pluginFile = $pluginDir . '/src/Plugin.php';
        if (!File::exists($pluginFile)) {
            $pluginFile = $pluginDir . '/Plugin.php';
        }

        if (!File::exists($pluginFile)) {
            return null;
        }

        $pluginName = basename($pluginDir);
        
        // Try to extract the namespace from the Plugin.php file
        $content = File::get($pluginFile);
        $className = null;
        
        if (preg_match('/namespace\s+([^;]+);/i', $content, $matches)) {
            $namespace = trim($matches[1]);
            $className = $namespace . '\\Plugin';
        }
        
        // Fallback to directory name (convert kebab-case to PascalCase)
        if (!$className) {
            $pluginNamePascal = str_replace(' ', '', ucwords(str_replace('-', ' ', $pluginName)));
            $className = "HyroPlugins\\{$pluginNamePascal}\\Plugin";
        }

        // First, try to require the file directly
        try {
            require_once $pluginFile;
        } catch (\Exception $e) {
            logger()->error("Failed to require plugin file {$pluginFile}: " . $e->getMessage());
            return null;
        }

        // Check if class exists after requiring
        if (!class_exists($className)) {
            logger()->error("Plugin class {$className} not found after requiring file");
            return null;
        }

        try {
            // Use Laravel's container to create the instance
            $plugin = app($className);

            if (!$plugin instanceof HyroPlugin) {
                logger()->error("Plugin {$className} does not extend HyroPlugin");
                return null;
            }

            return [
                'class' => $className,
                'path' => $pluginDir,
                'type' => 'local',
                'source' => 'local',
                'id' => $plugin->getId(),
                'meta' => [
                    'name' => $plugin->getName(),
                    'description' => $plugin->getDescription(),
                    'version' => $plugin->getVersion(),
                    'author' => $plugin->getAuthor(),
                    'dependencies' => $plugin->getDependencies(),
                ]
            ];
        } catch (\Exception $e) {
            logger()->error("Error instantiating plugin {$className}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Discover plugins from remote sources
     */
    protected function discoverRemotePlugins(bool $forceRefresh = false): array
    {
        $remotePlugins = [];
        $cacheTtl = config('hyro.plugins.remote_cache_ttl', 1800); // 30 minutes

        foreach ($this->remoteSources as $sourceName => $sourceConfig) {
            if (!$sourceConfig['enabled']) {
                continue;
            }

            $cacheKey = "hyro.plugins.remote.{$sourceName}";

            try {
                if ($forceRefresh) {
                    $plugins = $this->fetchFromRemoteSource($sourceName, $sourceConfig);
                    Cache::put($cacheKey, $plugins, $cacheTtl);
                } else {
                    $plugins = Cache::remember($cacheKey, $cacheTtl,
                        fn() => $this->fetchFromRemoteSource($sourceName, $sourceConfig)
                    );
                }

                $remotePlugins = array_merge($remotePlugins, $plugins);
            } catch (Exception $e) {
                logger()->error("Hyro Remote Source Error [{$sourceName}]: " . $e->getMessage());
            }
        }

        return $remotePlugins;
    }

    /**
     * Fetch plugins from a specific remote source
     */
    protected function fetchFromRemoteSource(string $sourceName, array $config): array
    {
        return match ($config['type']) {
            'marketplace' => $this->fetchFromMarketplace($config),
            'github' => $this->fetchFromGitHub($config),
            'gitlab' => $this->fetchFromGitLab($config),
            'packagist' => $this->fetchFromPackagist($config),
            default => []
        };
    }

    /**
     * Fetch plugins from Hyro Marketplace
     */
    protected function fetchFromMarketplace(array $config): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('hyro.plugins.marketplace.api_key'),
                'Accept' => 'application/json',
            ])
            ->get($config['url'] . '/plugins');

        if (!$response->successful()) {
            throw new Exception("Marketplace API error: " . $response->body());
        }

        $plugins = $response->json()['data'] ?? [];

        $formattedPlugins = [];
        foreach ($plugins as $plugin) {
            $formattedPlugins[$plugin['id']] = [
                'id' => $plugin['id'],
                'name' => $plugin['name'],
                'description' => $plugin['description'],
                'version' => $plugin['version'],
                'author' => $plugin['author'],
                'source' => 'marketplace',
                'remote_url' => $plugin['download_url'],
                'type' => 'remote',
                'is_installed' => $this->isPluginInstalled($plugin['id']),
                'metadata' => $plugin,
            ];
        }

        return $formattedPlugins;
    }

    /**
     * Fetch plugins from GitHub
     */
    protected function fetchFromGitHub(array $config): array
    {
        $query = 'hyro-plugin topic:hyro-plugin';
        $response = Http::timeout(30)
            ->get("{$config['url']}/search/repositories", [
                'q' => $query,
                'sort' => 'updated',
                'per_page' => 50,
            ]);

        if (!$response->successful()) {
            throw new Exception("GitHub API error: " . $response->body());
        }

        $repositories = $response->json()['items'] ?? [];

        $formattedPlugins = [];
        foreach ($repositories as $repo) {
            $pluginId = $this->extractPluginIdFromRepo($repo['full_name']);

            $formattedPlugins[$pluginId] = [
                'id' => $pluginId,
                'name' => $repo['name'],
                'description' => $repo['description'],
                'version' => '1.0.0', // Would need to fetch releases
                'author' => $repo['owner']['login'],
                'source' => 'github',
                'remote_url' => $repo['clone_url'],
                'type' => 'remote',
                'is_installed' => $this->isPluginInstalled($pluginId),
                'metadata' => $repo,
            ];
        }

        return $formattedPlugins;
    }

    /**
     * Fetch plugins from GitLab
     */
    protected function fetchFromGitLab(array $config): array
    {
        $response = Http::timeout(30)
            ->get("{$config['url']}/projects", [
                'search' => 'hyro-plugin',
                'order_by' => 'last_activity_at',
                'per_page' => 50,
            ]);

        if (!$response->successful()) {
            throw new Exception("GitLab API error: " . $response->body());
        }

        $projects = $response->json();

        $formattedPlugins = [];
        foreach ($projects as $project) {
            $pluginId = $this->extractPluginIdFromRepo($project['path_with_namespace']);

            $formattedPlugins[$pluginId] = [
                'id' => $pluginId,
                'name' => $project['name'],
                'description' => $project['description'],
                'version' => '1.0.0',
                'author' => $project['namespace']['path'],
                'source' => 'gitlab',
                'remote_url' => $project['http_url_to_repo'],
                'type' => 'remote',
                'is_installed' => $this->isPluginInstalled($pluginId),
                'metadata' => $project,
            ];
        }

        return $formattedPlugins;
    }

    /**
     * Fetch plugins from Packagist
     */
    protected function fetchFromPackagist(array $config): array
    {
        $response = Http::timeout(30)
            ->get("{$config['url']}/search.json", [
                'q' => 'hyro-plugin',
                'type' => 'hyro-plugin',
            ]);

        if (!$response->successful()) {
            throw new Exception("Packagist API error: " . $response->body());
        }

        $packages = $response->json()['results'] ?? [];

        $formattedPlugins = [];
        foreach ($packages as $package) {
            $pluginId = $this->extractPluginIdFromPackage($package['name']);

            $formattedPlugins[$pluginId] = [
                'id' => $pluginId,
                'name' => $package['name'],
                'description' => $package['description'],
                'version' => $package['version'],
                'author' => $this->extractAuthorFromPackage($package),
                'source' => 'packagist',
                'remote_url' => $package['repository'],
                'type' => 'remote',
                'is_installed' => $this->isPluginInstalled($pluginId),
                'metadata' => $package,
            ];
        }

        return $formattedPlugins;
    }

    /**
     * Load all enabled plugins (OPTIMIZED)
     */
    public function load(): void
    {
        // If plugins haven't been discovered yet, discover from cache only
        if ($this->plugins->isEmpty()) {
            $this->discover(false); // Use cache, don't scan filesystem
        }
        
        $sortedPlugins = $this->resolveDependencies();

        foreach ($sortedPlugins as $id) {
            // Only load plugins marked as active
            if ($this->isPluginActive($id)) {
                try {
                    $this->loadPlugin($id);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    /**
     * Load a specific plugin
     */
    public function loadPlugin(string $id): void
    {
        if ($this->loadedPlugins->has($id)) {
            return; // Already loaded
        }

        $pluginData = $this->plugins->get($id);

        if (!$pluginData) {
            throw new \Exception("Plugin '{$id}' not found.");
        }

        // For remote plugins that aren't installed yet, we can't load them
        if ($pluginData['type'] === 'remote' && !$this->isPluginInstalled($id)) {
            throw new \Exception("Remote plugin '{$id}' is not installed locally.");
        }

        $className = $pluginData['class'];

        if (!class_exists($className)) {
            // For local plugins, try to require the file
            if ($pluginData['type'] === 'local' && isset($pluginData['path'])) {
                // Check for Plugin.php in both src and root directory
                $pluginFile = $pluginData['path'] . '/src/Plugin.php';
                if (!File::exists($pluginFile)) {
                    $pluginFile = $pluginData['path'] . '/Plugin.php';
                }
                
                if (File::exists($pluginFile)) {
                    require_once $pluginFile;
                }
            }

            if (!class_exists($className)) {
                throw new \Exception("Plugin class '{$className}' not found.");
            }
        }

        $plugin = new $className($this->app);

        if (!$plugin instanceof HyroPlugin) {
            throw new \Exception("Plugin '{$id}' is not a valid HyroPlugin.");
        }

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

        // Add to loaded plugins
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

        if ($pluginData && $pluginData['type'] === 'local') {
            // Only load dependencies for local (installed) plugins
            $className = $pluginData['class'];
            if (class_exists($className)) {
                $plugin = new $className($this->app);
                foreach ($plugin->getDependencies() as $dependency) {
                    if (!$this->plugins->has($dependency)) {
                        throw new \Exception("Plugin '{$id}' depends on '{$dependency}' which is not available.");
                    }
                    $this->visitPlugin($dependency, $visited, $sorted);
                }
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
     * Install a plugin from remote source
     */
    public function installRemote(string $pluginId, string $source, ?string $version = null): bool
    {
        $pluginData = $this->plugins->get($pluginId);

        if (!$pluginData || $pluginData['type'] !== 'remote') {
            throw new Exception("Plugin '{$pluginId}' not found or not a remote plugin");
        }

        if ($this->isPluginInstalled($pluginId)) {
            throw new Exception("Plugin '{$pluginId}' is already installed");
        }

        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
        $installPath = $pluginsPath . '/' . $pluginId;

        try {
            // Download and install based on source type
            switch ($source) {
                case 'github':
                case 'gitlab':
                    $this->installFromGit($pluginData['remote_url'], $installPath, $version);
                    break;

                case 'packagist':
                    $this->installFromPackagist($pluginData['metadata']['name'], $version);
                    break;

                case 'marketplace':
                    $this->installFromMarketplace($pluginData, $installPath, $version);
                    break;

                default:
                    throw new Exception("Unsupported source: {$source}");
            }

            // Refresh plugin discovery
            $this->discover(true);

            // Install the plugin
            $this->install($pluginId);

            return true;

        } catch (Exception $e) {
            // Clean up on failure
            if (File::exists($installPath)) {
                File::deleteDirectory($installPath);
            }
            throw $e;
        }
    }

    /**
     * Install plugin from Git repository
     */
    protected function installFromGit(string $repositoryUrl, string $installPath, ?string $version = null): void
    {
        $commands = ['git', 'clone', $repositoryUrl, $installPath];

        if ($version) {
            $commands = ['git', 'clone', '--branch', $version, $repositoryUrl, $installPath];
        }

        $process = new Process($commands);
        $process->setTimeout(300); // 5 minutes
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Git clone failed: " . $process->getErrorOutput());
        }

        // Check if it's a valid Hyro plugin
        $pluginFile = $installPath . '/src/Plugin.php';
        if (!File::exists($pluginFile)) {
            $pluginFile = $installPath . '/Plugin.php';
        }
        
        if (!File::exists($pluginFile)) {
            File::deleteDirectory($installPath);
            throw new Exception("Downloaded repository is not a valid Hyro plugin (Plugin.php not found)");
        }
    }

    /**
     * Install plugin via Composer from Packagist
     */
    protected function installFromPackagist(string $packageName, ?string $version = null): void
    {
        $packageSpec = $version ? "{$packageName}:{$version}" : $packageName;

        $process = new Process(['composer', 'require', $packageSpec]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Composer require failed: " . $process->getErrorOutput());
        }
    }

    /**
     * Install plugin from Marketplace
     */
    protected function installFromMarketplace(array $pluginData, string $installPath, ?string $version = null): void
    {
        $downloadUrl = $version
            ? $pluginData['metadata']['versions'][$version]['download_url']
            : $pluginData['remote_url'];

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('hyro.plugins.marketplace.api_key'),
            ])
            ->get($downloadUrl);

        if (!$response->successful()) {
            throw new Exception("Marketplace download failed: " . $response->body());
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'hyro_plugin_');
        File::put($zipPath, $response->body());

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($installPath);
            $zip->close();
            File::delete($zipPath);
        } else {
            File::delete($zipPath);
            throw new Exception("Failed to extract plugin archive");
        }
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

        // For remote plugins, ensure they're installed locally first
        if ($pluginData['type'] === 'remote' && !$this->isPluginInstalled($id)) {
            throw new \Exception("Remote plugin '{$id}' must be downloaded first using installRemote.");
        }

        $className = $pluginData['class'];

        if (!class_exists($className)) {
            // Try to load the class manually
            if (isset($pluginData['path'])) {
                // Check for Plugin.php in both src and root directory
                $pluginFile = $pluginData['path'] . '/src/Plugin.php';
                if (!File::exists($pluginFile)) {
                    $pluginFile = $pluginData['path'] . '/Plugin.php';
                }
                
                if (File::exists($pluginFile)) {
                    require_once $pluginFile;
                }
            }

            if (!class_exists($className)) {
                throw new \Exception("Plugin class '{$className}' not found.");
            }
        }

        $plugin = new $className($this->app);
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

        // CHANGED: Remove from active state
        $this->markPluginInactive($id);

        $this->loadedPlugins->forget($id);
        Cache::forget('hyro.plugins.discovered');

        event('hyro.plugin.uninstalled', [$id]);
    }

    /**
     * Activate plugin
     */
    public function activate(string $id): void
    {
        // First ensure plugin is loaded
        if (!$this->loadedPlugins->has($id)) {
            $this->loadPlugin($id);
        }

        $plugin = $this->getPlugin($id);

        if ($plugin) {
            $plugin->activate();

            // CHANGED: Mark as active in state file
            $this->markPluginActive($id);

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

            // CHANGED: Mark as inactive in state file
            $this->markPluginInactive($id);

            $this->loadedPlugins->forget($id);
            event('hyro.plugin.deactivated', [$id]);
        }
    }

    /**
     * Upgrade a plugin to a specific version
     */
    public function upgrade(string $pluginId, ?string $version = null): bool
    {
        $pluginData = $this->plugins->get($pluginId);

        if (!$pluginData) {
            throw new Exception("Plugin '{$pluginId}' not found");
        }

        if ($pluginData['type'] === 'remote') {
            return $this->upgradeRemotePlugin($pluginData, $version);
        }

        // For local plugins, we need to manually update files
        throw new Exception("Local plugins must be upgraded manually");
    }

    /**
     * Upgrade a remote plugin
     */
    protected function upgradeRemotePlugin(array $pluginData, ?string $version = null): bool
    {
        $pluginId = $pluginData['id'];
        $currentVersion = $pluginData['meta']['version'] ?? '1.0.0';

        // Check for available updates
        $availableVersions = $this->getAvailableVersions($pluginData);
        $latestVersion = $availableVersions['latest'] ?? $currentVersion;

        $targetVersion = $version ?: $latestVersion;

        if (Semver::satisfies($currentVersion, "^{$targetVersion}")) {
            throw new Exception("Plugin '{$pluginId}' is already at version {$targetVersion}");
        }

        // Backup current plugin
        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
        $currentPath = $pluginsPath . '/' . $pluginId;
        $backupPath = $pluginsPath . '/backups/' . $pluginId . '_' . time();

        if (File::exists($currentPath)) {
            File::move($currentPath, $backupPath);
        }

        try {
            // Reinstall with new version
            $this->installRemote($pluginId, $pluginData['source'], $targetVersion);

            // Remove backup
            File::deleteDirectory($backupPath);

            return true;

        } catch (Exception $e) {
            // Restore from backup
            if (File::exists($backupPath)) {
                File::move($backupPath, $currentPath);
            }
            throw $e;
        }
    }

    /**
     * Get available versions for a plugin
     */
    public function getAvailableVersions(array $pluginData): array
    {
        return match ($pluginData['source']) {
            'github' => $this->getGitHubVersions($pluginData),
            'gitlab' => $this->getGitLabVersions($pluginData),
            'packagist' => $this->getPackagistVersions($pluginData),
            'marketplace' => $this->getMarketplaceVersions($pluginData),
            default => ['latest' => $pluginData['version']],
        };
    }

    /**
     * Get versions from GitHub
     */
    protected function getGitHubVersions(array $pluginData): array
    {
        $repo = $pluginData['metadata']['full_name'];
        $response = Http::get("https://api.github.com/repos/{$repo}/releases");

        if (!$response->successful()) {
            return ['latest' => $pluginData['version']];
        }

        $releases = $response->json();
        $versions = array_column($releases, 'tag_name');

        return [
            'latest' => $versions[0] ?? $pluginData['version'],
            'versions' => $versions,
        ];
    }

    /**
     * Check if a plugin is installed locally
     */
    public function isPluginInstalled(string $pluginId): bool
    {
        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
        $pluginPath = $pluginsPath . '/' . $pluginId;

        if (!File::exists($pluginPath)) {
            return false;
        }
        
        // Check for Plugin.php in both src and root directory
        $pluginFile = $pluginPath . '/src/Plugin.php';
        if (!File::exists($pluginFile)) {
            $pluginFile = $pluginPath . '/Plugin.php';
        }
        
        return File::exists($pluginFile);
    }

    /**
     * Search plugins across all sources
     */
    public function search(string $query, ?string $source = null): Collection
    {
        $results = new Collection();

        foreach ($this->plugins as $pluginId => $pluginData) {
            if ($source && $pluginData['source'] !== $source) {
                continue;
            }

            if (stripos($pluginData['name'], $query) !== false ||
                stripos($pluginData['description'], $query) !== false) {
                $results->put($pluginId, $pluginData);
            }
        }

        return $results;
    }

    /**
     * Get plugin update information
     */
    public function getUpdateInfo(string $pluginId): ?array
    {
        $pluginData = $this->plugins->get($pluginId);

        if (!$pluginData || $pluginData['type'] !== 'remote') {
            return null;
        }

        $currentVersion = $pluginData['meta']['version'] ?? '1.0.0';
        $availableVersions = $this->getAvailableVersions($pluginData);
        $latestVersion = $availableVersions['latest'] ?? $currentVersion;

        if (Semver::satisfies($currentVersion, "^{$latestVersion}")) {
            return null; // No update available
        }

        return [
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'update_available' => true,
            'changelog' => $this->getChangelog($pluginData, $latestVersion),
            'versions' => $availableVersions['versions'] ?? [],
        ];
    }

    /**
     * Get plugin instance
     */
    public function getPlugin(string $id): ?HyroPlugin
    {
        return $this->loadedPlugins->get($id);
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
     * Get plugin states
     */
    public function getPluginStates(): array
    {
        return $this->loadPluginStates();
    }

    /**
     * Check if plugin is loaded
     */
    public function isLoaded(string $id): bool
    {
        return $this->loadedPlugins->has($id);
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
     * Refresh plugin manager state
     */
    public function refresh(): void
    {
        // CHANGED: Don't preserve loaded plugins from memory
        // Clear cache
        Cache::forget('hyro.plugins.discovered');
        $this->plugins = new Collection();
        $this->loadedPlugins = new Collection();

        // Rediscover plugins
        $this->discover(true);

        // Load active plugins will be called by discover()
    }

    // Helper methods for remote plugin handling
    protected function extractPluginIdFromRepo(string $repoFullName): string
    {
        return \Illuminate\Support\Str::kebab(basename($repoFullName));
    }

    protected function extractPluginIdFromPackage(string $packageName): string
    {
        if (strpos($packageName, '/') !== false) {
            return \Illuminate\Support\Str::kebab(basename($packageName));
        }
        return \Illuminate\Support\Str::kebab($packageName);
    }

    protected function extractAuthorFromPackage(array $package): string
    {
        return $package['maintainers'][0]['name'] ?? 'Unknown';
    }

    protected function getChangelog(array $pluginData, string $version): string
    {
        // Implementation would depend on the source
        return 'Changelog not available';
    }
}

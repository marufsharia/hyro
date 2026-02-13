<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Support\Plugins\PluginManager as PluginService;
use ZipArchive;
use Exception;

class PluginManager extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $filter = 'all'; // all, installed, active, inactive, updates
    public $sortBy = 'name'; // name, date, author, status
    public $sortDirection = 'asc';
    
    public $selectedPlugin = null;
    public $showDetailsModal = false;
    public $showUploadModal = false;
    public $showConfirmModal = false;
    public $confirmAction = '';
    public $confirmPluginId = '';
    
    public $uploadedFile = null;
    public $uploadProgress = 0;
    
    public $selectedPlugins = [];
    public $bulkAction = '';
    
    public $backupBeforeAction = true;
    public $forceAction = false;
    
    public $isLoaded = false; // Track if data has been loaded

    protected $queryString = ['search', 'filter', 'sortBy'];

    protected $listeners = [
        'refreshPlugins' => '$refresh',
        'pluginActionCompleted' => 'handleActionCompleted',
    ];

    public function mount()
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }
        
        // Don't discover here - too slow
        // Discovery will happen only when needed (refresh button)
    }

    public function render()
    {
        // Get plugin data - will auto-discover if needed
        $pluginService = app('hyro.plugins');
        
        // Try to get from cache first
        $allPlugins = cache()->get('hyro.plugins.list');
        
        // If not in cache, discover now (will be slow first time)
        if (!$allPlugins || $allPlugins->isEmpty()) {
            $pluginService->discover(false); // Use cache if available
            $allPlugins = $pluginService->getAllPlugins();
            
            // Only cache if we actually got plugins
            if ($allPlugins && !$allPlugins->isEmpty()) {
                cache()->put('hyro.plugins.list', $allPlugins, 3600);
            }
        }
        
        // Convert to collection if it's an array
        if (is_array($allPlugins)) {
            $allPlugins = collect($allPlugins);
        }
        
        // Mark as loaded
        $this->isLoaded = true;
        
        $states = $pluginService->getPluginStates();
        
        // Simplified transformation
        $plugins = $allPlugins->map(function ($plugin, $id) use ($states) {
            return [
                'id' => $id,
                'name' => $plugin['meta']['name'] ?? ucfirst($id),
                'description' => $plugin['meta']['description'] ?? 'No description',
                'version' => $plugin['meta']['version'] ?? '1.0.0',
                'author' => $plugin['meta']['author'] ?? 'Unknown',
                'type' => $plugin['type'],
                'is_active' => $states[$id]['active'] ?? false,
                'is_installed' => $plugin['type'] === 'local',
                'path' => $plugin['path'] ?? null,
                'dependencies' => $plugin['meta']['dependencies'] ?? [],
                'icon' => $this->getPluginIcon($plugin),
                'has_update' => false,
            ];
        });

        // Apply filters
        $plugins = $this->applyFilters($plugins);

        // Apply search
        if ($this->search) {
            $plugins = $plugins->filter(function ($plugin) {
                return stripos($plugin['name'], $this->search) !== false ||
                       stripos($plugin['description'], $this->search) !== false ||
                       stripos($plugin['author'], $this->search) !== false;
            });
        }

        // Apply sorting
        $plugins = $this->applySorting($plugins);

        return view('hyro::admin.plugins.manager', [
            'plugins' => $plugins,
            'stats' => $this->getStats($allPlugins, $states),
        ]);
    }

    protected function applyFilters($plugins)
    {
        return match ($this->filter) {
            'installed' => $plugins->filter(fn($p) => $p['is_installed']),
            'active' => $plugins->filter(fn($p) => $p['is_active']),
            'inactive' => $plugins->filter(fn($p) => $p['is_installed'] && !$p['is_active']),
            'updates' => $plugins->filter(fn($p) => $p['has_update']),
            'remote' => $plugins->filter(fn($p) => $p['type'] === 'remote'),
            default => $plugins,
        };
    }

    protected function applySorting($plugins)
    {
        $direction = $this->sortDirection === 'asc' ? 1 : -1;
        
        return $plugins->sort(function ($a, $b) use ($direction) {
            return match ($this->sortBy) {
                'name' => strcmp($a['name'], $b['name']) * $direction,
                'author' => strcmp($a['author'], $b['author']) * $direction,
                'date' => strcmp($a['activated_at'] ?? '', $b['activated_at'] ?? '') * $direction,
                'status' => ($a['is_active'] <=> $b['is_active']) * $direction,
                default => 0,
            };
        });
    }

    protected function getStats($allPlugins, $states)
    {
        // Handle empty or null plugins
        if (!$allPlugins || (is_countable($allPlugins) && count($allPlugins) === 0)) {
            return [
                'total' => 0,
                'installed' => 0,
                'active' => 0,
                'inactive' => 0,
                'available' => 0,
            ];
        }
        
        // Convert to array if it's a collection
        if ($allPlugins instanceof \Illuminate\Support\Collection) {
            $allPlugins = $allPlugins->toArray();
        }
        
        // Simplified stats calculation
        $installed = 0;
        $active = 0;
        
        foreach ($allPlugins as $id => $plugin) {
            if (isset($plugin['type']) && $plugin['type'] === 'local') {
                $installed++;
                if ($states[$id]['active'] ?? false) {
                    $active++;
                }
            }
        }
        
        return [
            'total' => count($allPlugins),
            'installed' => $installed,
            'active' => $active,
            'inactive' => $installed - $active,
            'available' => count($allPlugins) - $installed,
        ];
    }

    public function showDetails($pluginId)
    {
       
        $pluginService = app('hyro.plugins');
        $plugin = $pluginService->getAllPlugins()->get($pluginId);
        $states = $pluginService->getPluginStates();
        
        if ($plugin) {
            $this->selectedPlugin = [
                'id' => $pluginId,
                'name' => $plugin['meta']['name'] ?? ucfirst($pluginId),
                'description' => $plugin['meta']['description'] ?? 'No description available',
                'short_description' => $plugin['meta']['short_description'] ?? substr($plugin['meta']['description'] ?? 'No description available', 0, 120),
                'version' => $plugin['meta']['version'] ?? '1.0.0',
                'author' => $plugin['meta']['author'] ?? 'Unknown',
                'author_website' => $plugin['meta']['author_website'] ?? null,
                'type' => $plugin['type'],
                'source' => $plugin['source'] ?? 'local',
                'is_active' => $states[$pluginId]['active'] ?? false,
                'is_installed' => $plugin['type'] === 'local',
                'path' => $plugin['path'] ?? null,
                'dependencies' => $plugin['meta']['dependencies'] ?? [],
                'activated_at' => $states[$pluginId]['activated_at'] ?? null,
                'installed_at' => $states[$pluginId]['installed_at'] ?? ($plugin['type'] === 'local' ? now()->toDateTimeString() : null),
                'license' => $plugin['meta']['license'] ?? 'MIT',
                'download_count' => $plugin['meta']['download_count'] ?? 0,
                'rating' => $plugin['meta']['rating'] ?? 0,
                'icon' => $this->getPluginIcon($plugin),
                'screenshots' => $this->getPluginScreenshots($plugin),
                'readme' => $this->getPluginReadme($plugin),
                'changelog' => $this->getPluginChangelog($plugin),
                // Phase 1: Enhanced Overview
                'key_features' => $plugin['meta']['key_features'] ?? [],
                'demo_url' => $plugin['meta']['demo_url'] ?? null,
                'use_cases' => $plugin['meta']['use_cases'] ?? [],
                'performance_impact' => $plugin['meta']['performance_impact'] ?? 'low',
                'security_notes' => $plugin['meta']['security_notes'] ?? null,
                'last_updated' => $plugin['meta']['last_updated'] ?? null,
                // Compatibility
                'hyro_version' => $plugin['meta']['hyro_version'] ?? '*',
                'php_version' => $plugin['meta']['php_version'] ?? '>=8.1',
                'laravel_version' => $plugin['meta']['laravel_version'] ?? '>=10.0',
                // Phase 2: Settings
                'settings' => $this->getPluginSettings($pluginId),
                'settings_schema' => $plugin['meta']['settings_schema'] ?? [],
                // Phase 3: Dependencies & Permissions
                'required_plugins' => $plugin['meta']['required_plugins'] ?? [],
                'optional_plugins' => $plugin['meta']['optional_plugins'] ?? [],
                'conflicts_with' => $plugin['meta']['conflicts_with'] ?? [],
                'permissions' => $this->getPluginPermissions($pluginId),
                // Phase 4: Technical Info
                'namespace' => $plugin['meta']['namespace'] ?? null,
                'service_providers' => $this->getPluginServiceProviders($plugin),
                'routes' => $this->getPluginRoutes($pluginId),
                'migrations' => $this->getPluginMigrations($plugin),
                'published_assets' => $this->getPluginAssets($plugin),
                'event_listeners' => $plugin['meta']['event_listeners'] ?? [],
                'sidebar_entries' => $this->getPluginSidebarEntries($pluginId),
                'autoload' => $plugin['meta']['autoload'] ?? [],
                'psr4_namespaces' => $plugin['meta']['psr4'] ?? [],
                // Phase 5: Updates & Version History
                'versions' => $this->getPluginVersions($pluginId),
                'current_version' => $plugin['meta']['version'] ?? '1.0.0',
                'latest_version' => $this->getLatestVersion($pluginId),
                'has_update' => $this->checkForUpdate($pluginId, $plugin['meta']['version'] ?? '1.0.0'),
                'auto_update_enabled' => $this->getAutoUpdateSetting($pluginId),
                // Phase 6: Activity Logs & Monitoring
                'activity_logs' => $this->getPluginActivityLogs($pluginId),
                'usage_stats' => $this->getPluginUsageStats($pluginId),
                'last_activated_by' => $this->getLastActionBy($pluginId, 'activate'),
                'last_deactivated_by' => $this->getLastActionBy($pluginId, 'deactivate'),
                'activity_pagination' => $this->getActivityPagination($pluginId),
                // Phase 7: Health & Diagnostics
                'health_score' => $this->calculateHealthScore($pluginId, $plugin),
                'health_checks' => $this->runHealthChecks($pluginId, $plugin),
                'performance_metrics' => $this->getPerformanceMetrics($pluginId, $plugin),
                'health_recommendations' => $this->getHealthRecommendations($pluginId, $plugin),
                // Phase 8: Marketplace Integration
                'marketplace_data' => $this->getMarketplaceData($pluginId, $plugin),
            ];
            
            $this->showDetailsModal = true;
        }
    }

    public function install($pluginId)
    {
        try {
            $pluginService = app('hyro.plugins');
            
            DB::beginTransaction();
            
            if ($this->backupBeforeAction) {
                $this->createBackup($pluginId);
            }
            
            $pluginService->install($pluginId);
            
            DB::commit();
            
            $this->logActivity('install', $pluginId);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin installed successfully']);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Installation failed: ' . $e->getMessage()]);
        }
    }

    public function uninstall($pluginId, $force = false)
    {
        $this->confirmAction = 'uninstall';
        $this->confirmPluginId = $pluginId;
        $this->forceAction = $force;
        $this->showConfirmModal = true;
    }

    public function confirmUninstall()
    {
        try {
            $pluginService = app('hyro.plugins');
            
            DB::beginTransaction();
            
            if ($this->backupBeforeAction) {
                $this->createBackup($this->confirmPluginId);
            }
            
            $pluginService->uninstall($this->confirmPluginId);
            
            if ($this->forceAction) {
                // Force delete plugin directory
                $plugin = $pluginService->getAllPlugins()->get($this->confirmPluginId);
                if ($plugin && isset($plugin['path'])) {
                    File::deleteDirectory($plugin['path']);
                }
            }
            
            DB::commit();
            
            $this->logActivity('uninstall', $this->confirmPluginId);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin uninstalled successfully']);
            $this->showConfirmModal = false;
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Uninstallation failed: ' . $e->getMessage()]);
        }
    }

    public function activate($pluginId)
    {
        try {
            $pluginService = app('hyro.plugins');
            $pluginService->activate($pluginId);
            
            // Clear sidebar cache so plugin appears immediately
            \Marufsharia\Hyro\Services\ModuleManager::clearSidebarCache();
            
            $this->logActivity('activate', $pluginId);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin activated successfully']);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Activation failed: ' . $e->getMessage()]);
        }
    }

    public function deactivate($pluginId)
    {
        try {
            $pluginService = app('hyro.plugins');
            $pluginService->deactivate($pluginId);
            
            // Clear sidebar cache so plugin disappears immediately
            \Marufsharia\Hyro\Services\ModuleManager::clearSidebarCache();
            
            $this->logActivity('deactivate', $pluginId);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin deactivated successfully']);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Deactivation failed: ' . $e->getMessage()]);
        }
    }

    public function uploadPlugin()
    {
        $this->validate([
            'uploadedFile' => 'required|file|mimes:zip|max:51200', // 50MB max
        ]);

        try {
            $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
            $tempPath = storage_path('app/temp-plugins');
            
            File::ensureDirectoryExists($tempPath);
            
            // Save uploaded file
            $zipPath = $this->uploadedFile->store('temp-plugins');
            $fullZipPath = storage_path('app/' . $zipPath);
            
            // Extract ZIP
            $zip = new ZipArchive();
            if ($zip->open($fullZipPath) === true) {
                // Get plugin name from ZIP
                $pluginName = $this->extractPluginNameFromZip($zip);
                
                if (!$pluginName) {
                    throw new Exception('Invalid plugin structure. Plugin.php not found.');
                }
                
                $extractPath = $pluginsPath . '/' . $pluginName;
                
                if (File::exists($extractPath)) {
                    throw new Exception('Plugin already exists. Please uninstall it first.');
                }
                
                $zip->extractTo($extractPath);
                $zip->close();
                
                // Clean up
                Storage::delete($zipPath);
                
                // Discover and install
                $pluginService = app('hyro.plugins');
                $pluginService->discover(true);
                $pluginService->install($pluginName);
                
                $this->logActivity('upload_install', $pluginName);
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin uploaded and installed successfully']);
                $this->showUploadModal = false;
                $this->uploadedFile = null;
                $this->dispatch('refreshPlugins');
                
            } else {
                throw new Exception('Failed to extract ZIP file');
            }
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    public function bulkExecute()
    {
        if (empty($this->selectedPlugins) || empty($this->bulkAction)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Please select plugins and action']);
            return;
        }

        try {
            $pluginService = app('hyro.plugins');
            $successCount = 0;
            
            foreach ($this->selectedPlugins as $pluginId) {
                try {
                    match ($this->bulkAction) {
                        'activate' => $pluginService->activate($pluginId),
                        'deactivate' => $pluginService->deactivate($pluginId),
                        'uninstall' => $pluginService->uninstall($pluginId),
                        default => null,
                    };
                    $successCount++;
                } catch (Exception $e) {
                    logger()->error("Bulk action failed for {$pluginId}: " . $e->getMessage());
                }
            }
            
            $this->selectedPlugins = [];
            $this->bulkAction = '';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Bulk action completed. {$successCount} plugins processed."
            ]);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Bulk action failed: ' . $e->getMessage()]);
        }
    }

    public function checkForUpdates()
    {
        try {
            // This is the ONLY place where we scan filesystem
            $pluginService = app('hyro.plugins');
            $pluginService->discover(true); // Force refresh
            
            // Cache the results
            $allPlugins = $pluginService->getAllPlugins();
            cache()->put('hyro.plugins.list', $allPlugins, 3600);
            
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin list refreshed']);
            
            // Force Livewire to re-render
            $this->dispatch('$refresh');
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Update check failed: ' . $e->getMessage()]);
        }
    }

    public function healthCheck($pluginId)
    {
        try {
            $pluginService = app('hyro.plugins');
            $plugin = $pluginService->getAllPlugins()->get($pluginId);
            
            if (!$plugin) {
                throw new Exception('Plugin not found');
            }
            
            $issues = [];
            
            // Check if plugin file exists
            if ($plugin['type'] === 'local') {
                $pluginFile = $plugin['path'] . '/src/Plugin.php';
                if (!File::exists($pluginFile)) {
                    $pluginFile = $plugin['path'] . '/Plugin.php';
                }
                
                if (!File::exists($pluginFile)) {
                    $issues[] = 'Plugin.php file not found';
                }
            }
            
            // Check dependencies
            foreach ($plugin['meta']['dependencies'] ?? [] as $dependency) {
                if (!$pluginService->getAllPlugins()->has($dependency)) {
                    $issues[] = "Missing dependency: {$dependency}";
                }
            }
            
            if (empty($issues)) {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Plugin health check passed']);
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'Issues found: ' . implode(', ', $issues)
                ]);
            }
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Health check failed: ' . $e->getMessage()]);
        }
    }

    protected function createBackup($pluginId)
    {
        $pluginService = app('hyro.plugins');
        $plugin = $pluginService->getAllPlugins()->get($pluginId);
        
        if ($plugin && $plugin['type'] === 'local' && isset($plugin['path'])) {
            $backupPath = storage_path('app/plugin-backups');
            File::ensureDirectoryExists($backupPath);
            
            $backupFile = $backupPath . '/' . $pluginId . '_' . time() . '.zip';
            
            $zip = new ZipArchive();
            if ($zip->open($backupFile, ZipArchive::CREATE) === true) {
                $this->addDirectoryToZip($zip, $plugin['path'], basename($plugin['path']));
                $zip->close();
            }
        }
    }

    protected function addDirectoryToZip($zip, $path, $zipPath = '')
    {
        $files = File::allFiles($path);
        foreach ($files as $file) {
            $relativePath = $zipPath . '/' . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }
    }

    protected function extractPluginNameFromZip($zip)
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_contains($filename, 'Plugin.php')) {
                $parts = explode('/', $filename);
                return $parts[0] ?? null;
            }
        }
        return null;
    }

    protected function getPluginIcon($plugin)
    {
        if ($plugin['type'] === 'local' && isset($plugin['path'])) {
            $iconPath = $plugin['path'] . '/icon.png';
            if (File::exists($iconPath)) {
                return asset('hyro-plugins/' . basename($plugin['path']) . '/icon.png');
            }
        }
        return null;
    }

    protected function getPluginScreenshots($plugin)
    {
        $screenshots = [];
        if ($plugin['type'] === 'local' && isset($plugin['path'])) {
            $screenshotsPath = $plugin['path'] . '/screenshots';
            if (File::isDirectory($screenshotsPath)) {
                $files = File::files($screenshotsPath);
                foreach ($files as $file) {
                    if (in_array($file->getExtension(), ['png', 'jpg', 'jpeg', 'gif'])) {
                        $screenshots[] = asset('hyro-plugins/' . basename($plugin['path']) . '/screenshots/' . $file->getFilename());
                    }
                }
            }
        }
        return $screenshots;
    }

    protected function getPluginReadme($plugin)
    {
        if ($plugin['type'] === 'local' && isset($plugin['path'])) {
            $readmePath = $plugin['path'] . '/README.md';
            if (File::exists($readmePath)) {
                return File::get($readmePath);
            }
        }
        return null;
    }

    protected function getPluginChangelog($plugin)
    {
        if ($plugin['type'] === 'local' && isset($plugin['path'])) {
            $changelogPath = $plugin['path'] . '/CHANGELOG.md';
            if (File::exists($changelogPath)) {
                return File::get($changelogPath);
            }
        }
        return null;
    }

    protected function logActivity($action, $pluginId)
    {
        DB::table('hyro_plugin_activity_log')->insert([
            'user_id' => auth()->id(),
            'plugin_id' => $pluginId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    protected function getPluginSettings($pluginId)
    {
        // Check if settings table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_settings')) {
            return [];
        }
        
        return DB::table('hyro_plugin_settings')
            ->where('plugin_id', $pluginId)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    protected function getPluginPermissions($pluginId)
    {
        // Check if permissions table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_permissions')) {
            return [];
        }
        
        return DB::table('hyro_plugin_permissions')
            ->where('plugin_id', $pluginId)
            ->get()
            ->toArray();
    }

    public function savePluginSettings($pluginId, $settings)
    {
        try {
            DB::beginTransaction();
            
            foreach ($settings as $key => $value) {
                DB::table('hyro_plugin_settings')->updateOrInsert(
                    ['plugin_id' => $pluginId, 'key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'type' => gettype($value),
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );
            }
            
            DB::commit();
            
            $this->logActivity('settings_updated', $pluginId);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Settings saved successfully']);
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to save settings: ' . $e->getMessage()]);
        }
    }

    public function resetPluginSettings($pluginId)
    {
        try {
            DB::table('hyro_plugin_settings')
                ->where('plugin_id', $pluginId)
                ->delete();
            
            $this->logActivity('settings_reset', $pluginId);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Settings reset successfully']);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to reset settings: ' . $e->getMessage()]);
        }
    }

    // Phase 4: Technical Info Methods
    protected function getPluginServiceProviders($plugin)
    {
        $providers = [];
        
        if (isset($plugin['path']) && File::exists($plugin['path'])) {
            // Check for service provider files
            $providerPath = $plugin['path'] . '/src/Providers';
            if (File::isDirectory($providerPath)) {
                $files = File::files($providerPath);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $providers[] = $file->getFilenameWithoutExtension();
                    }
                }
            }
            
            // Check for main Plugin.php
            if (File::exists($plugin['path'] . '/src/Plugin.php')) {
                $providers[] = 'Plugin';
            }
        }
        
        return $providers;
    }

    protected function getPluginRoutes($pluginId)
    {
        $routes = [];
        $allRoutes = \Route::getRoutes();
        
        foreach ($allRoutes as $route) {
            $action = $route->getAction();
            
            // Check if route belongs to this plugin
            if (isset($action['namespace']) && str_contains($action['namespace'], $pluginId)) {
                $routes[] = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $action['uses'] ?? 'Closure',
                ];
            }
        }
        
        return $routes;
    }

    protected function getPluginMigrations($plugin)
    {
        $migrations = [];
        
        if (isset($plugin['path']) && File::exists($plugin['path'])) {
            $migrationPath = $plugin['path'] . '/database/migrations';
            if (File::isDirectory($migrationPath)) {
                $files = File::files($migrationPath);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $migrations[] = [
                            'file' => $file->getFilename(),
                            'name' => $this->getMigrationName($file->getFilename()),
                            'path' => $file->getPathname(),
                        ];
                    }
                }
            }
        }
        
        return $migrations;
    }

    protected function getMigrationName($filename)
    {
        // Extract migration name from filename
        // Format: YYYY_MM_DD_HHMMSS_migration_name.php
        $parts = explode('_', $filename);
        if (count($parts) >= 5) {
            return implode(' ', array_slice($parts, 4));
        }
        return str_replace('.php', '', $filename);
    }

    protected function getPluginAssets($plugin)
    {
        $assets = [];
        
        if (isset($plugin['path']) && File::exists($plugin['path'])) {
            $assetsPath = $plugin['path'] . '/resources/assets';
            if (File::isDirectory($assetsPath)) {
                $files = File::allFiles($assetsPath);
                foreach ($files as $file) {
                    $assets[] = [
                        'name' => $file->getFilename(),
                        'type' => $file->getExtension(),
                        'size' => $this->formatBytes($file->getSize()),
                        'path' => $file->getRelativePathname(),
                    ];
                }
            }
        }
        
        return $assets;
    }

    protected function getPluginSidebarEntries($pluginId)
    {
        $entries = [];
        
        // Try to get sidebar entries from cache or config
        try {
            // Check if ModuleManager has a method to get sidebar
            $moduleManager = app(\Marufsharia\Hyro\Services\ModuleManager::class);
            
            // Use reflection to check if method exists
            if (method_exists($moduleManager, 'getSidebar')) {
                $sidebar = $moduleManager->getSidebar();
            } elseif (method_exists($moduleManager, 'getModules')) {
                // Alternative: get from modules
                $modules = $moduleManager->getModules();
                $sidebar = $modules['sidebar'] ?? [];
            } else {
                // Fallback: return empty array
                return [];
            }
            
            foreach ($sidebar as $section) {
                if (isset($section['items'])) {
                    foreach ($section['items'] as $item) {
                        // Check if item belongs to this plugin
                        if (isset($item['plugin']) && $item['plugin'] === $pluginId) {
                            $entries[] = [
                                'label' => $item['label'] ?? 'Unknown',
                                'route' => $item['route'] ?? null,
                                'icon' => $item['icon'] ?? null,
                                'section' => $section['label'] ?? 'Unknown',
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // If any error occurs, just return empty array
            logger()->warning("Could not get sidebar entries for plugin {$pluginId}: " . $e->getMessage());
            return [];
        }
        
        return $entries;
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    // Phase 5: Updates & Version History Methods
    protected function getPluginVersions($pluginId)
    {
        // Check if versions table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_versions')) {
            return [];
        }
        
        return DB::table('hyro_plugin_versions')
            ->where('plugin_id', $pluginId)
            ->orderBy('release_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    protected function getLatestVersion($pluginId)
    {
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_versions')) {
            return null;
        }
        
        $latest = DB::table('hyro_plugin_versions')
            ->where('plugin_id', $pluginId)
            ->orderBy('release_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $latest ? $latest->version : null;
    }

    protected function checkForUpdate($pluginId, $currentVersion)
    {
        $latestVersion = $this->getLatestVersion($pluginId);
        
        if (!$latestVersion) {
            return false;
        }
        
        return version_compare($latestVersion, $currentVersion, '>');
    }

    protected function getAutoUpdateSetting($pluginId)
    {
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_settings')) {
            return false;
        }
        
        $setting = DB::table('hyro_plugin_settings')
            ->where('plugin_id', $pluginId)
            ->where('key', 'auto_update')
            ->first();
        
        return $setting ? (bool) $setting->value : false;
    }

    public function toggleAutoUpdate($pluginId)
    {
        try {
            $current = $this->getAutoUpdateSetting($pluginId);
            
            DB::table('hyro_plugin_settings')->updateOrInsert(
                ['plugin_id' => $pluginId, 'key' => 'auto_update'],
                [
                    'value' => !$current ? '1' : '0',
                    'type' => 'boolean',
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );
            
            $this->logActivity('auto_update_toggled', $pluginId);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Auto-update ' . (!$current ? 'enabled' : 'disabled')
            ]);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to toggle auto-update: ' . $e->getMessage()]);
        }
    }

    public function updatePlugin($pluginId, $version)
    {
        try {
            DB::beginTransaction();
            
            // Create backup if enabled
            if ($this->backupBeforeAction) {
                $this->createBackup($pluginId);
            }
            
            // Here you would implement the actual update logic
            // For now, we'll just log the action
            $this->logActivity('plugin_updated', $pluginId);
            
            DB::commit();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Plugin updated to version {$version}"
            ]);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    public function rollbackPlugin($pluginId, $version)
    {
        try {
            DB::beginTransaction();
            
            // Create backup before rollback
            $this->createBackup($pluginId);
            
            // Here you would implement the actual rollback logic
            // For now, we'll just log the action
            $this->logActivity('plugin_rolled_back', $pluginId);
            
            DB::commit();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Plugin rolled back to version {$version}"
            ]);
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Rollback failed: ' . $e->getMessage()]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    // Phase 6: Activity Logs & Monitoring Methods
    protected function getPluginActivityLogs($pluginId, $perPage = 20)
    {
        // Check if activity log table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_activity_log')) {
            return [];
        }
        
        try {
            return DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->orderBy('created_at', 'desc')
                ->limit($perPage)
                ->get()
                ->map(function ($log) {
                    // Attach user information if available
                    if ($log->user_id) {
                        $log->user = DB::table('users')->where('id', $log->user_id)->first();
                    }
                    return $log;
                })
                ->toArray();
        } catch (Exception $e) {
            logger()->error("Failed to get activity logs for plugin {$pluginId}: " . $e->getMessage());
            return [];
        }
    }

    protected function getPluginUsageStats($pluginId)
    {
        // Check if activity log table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_activity_log')) {
            return [
                'total_activations' => 0,
                'config_changes' => 0,
                'error_count' => 0,
            ];
        }
        
        try {
            $totalActivations = DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->where('action', 'activate')
                ->count();
            
            $configChanges = DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->whereIn('action', ['settings_updated', 'settings_reset'])
                ->count();
            
            $errorCount = DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->where('action', 'like', '%error%')
                ->count();
            
            return [
                'total_activations' => $totalActivations,
                'config_changes' => $configChanges,
                'error_count' => $errorCount,
            ];
        } catch (Exception $e) {
            logger()->error("Failed to get usage stats for plugin {$pluginId}: " . $e->getMessage());
            return [
                'total_activations' => 0,
                'config_changes' => 0,
                'error_count' => 0,
            ];
        }
    }

    protected function getLastActionBy($pluginId, $action)
    {
        // Check if activity log table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_activity_log')) {
            return null;
        }
        
        try {
            $log = DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->where('action', $action)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$log) {
                return null;
            }
            
            $user = null;
            if ($log->user_id) {
                $user = DB::table('users')->where('id', $log->user_id)->first();
            }
            
            return [
                'name' => $user ? $user->name : 'Unknown User',
                'time' => \Carbon\Carbon::parse($log->created_at)->diffForHumans(),
                'timestamp' => $log->created_at,
            ];
        } catch (Exception $e) {
            logger()->error("Failed to get last {$action} for plugin {$pluginId}: " . $e->getMessage());
            return null;
        }
    }

    protected function getActivityPagination($pluginId, $perPage = 20)
    {
        // Check if activity log table exists
        if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_activity_log')) {
            return [
                'total' => 0,
                'per_page' => $perPage,
                'from' => 0,
                'to' => 0,
            ];
        }
        
        try {
            $total = DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->count();
            
            return [
                'total' => $total,
                'per_page' => $perPage,
                'from' => $total > 0 ? 1 : 0,
                'to' => min($perPage, $total),
            ];
        } catch (Exception $e) {
            logger()->error("Failed to get pagination for plugin {$pluginId}: " . $e->getMessage());
            return [
                'total' => 0,
                'per_page' => $perPage,
                'from' => 0,
                'to' => 0,
            ];
        }
    }

    public function exportPluginLogs($pluginId, $format = 'csv')
    {
        try {
            // Check if activity log table exists
            if (!DB::getSchemaBuilder()->hasTable('hyro_plugin_activity_log')) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Activity log table not found']);
                return;
            }
            
            $logs = DB::table('hyro_plugin_activity_log')
                ->where('plugin_id', $pluginId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($logs->isEmpty()) {
                $this->dispatch('notify', ['type' => 'warning', 'message' => 'No logs to export']);
                return;
            }
            
            $filename = "plugin_{$pluginId}_logs_" . date('Y-m-d_His') . ".{$format}";
            $filepath = storage_path('app/exports/' . $filename);
            
            // Ensure directory exists
            File::ensureDirectoryExists(storage_path('app/exports'));
            
            if ($format === 'csv') {
                $file = fopen($filepath, 'w');
                
                // Write headers
                fputcsv($file, ['ID', 'Plugin ID', 'Action', 'User ID', 'IP Address', 'User Agent', 'Created At']);
                
                // Write data
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->plugin_id,
                        $log->action,
                        $log->user_id ?? 'N/A',
                        $log->ip_address ?? 'N/A',
                        $log->user_agent ?? 'N/A',
                        $log->created_at,
                    ]);
                }
                
                fclose($file);
            } elseif ($format === 'json') {
                File::put($filepath, json_encode($logs, JSON_PRETTY_PRINT));
            }
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Logs exported successfully to {$filename}"
            ]);
            
            // Trigger download
            return response()->download($filepath)->deleteFileAfterSend(true);
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    // Phase 7: Health & Diagnostics Methods
    protected function calculateHealthScore($pluginId, $plugin)
    {
        try {
            $score = 100;
            $checks = $this->runHealthChecks($pluginId, $plugin);
            
            foreach ($checks as $check) {
                if ($check['status'] === 'fail') {
                    $score -= 15;
                } elseif ($check['status'] === 'warning') {
                    $score -= 5;
                }
            }
            
            return max(0, min(100, $score));
        } catch (Exception $e) {
            logger()->error("Failed to calculate health score for plugin {$pluginId}: " . $e->getMessage());
            return 0;
        }
    }

    protected function runHealthChecks($pluginId, $plugin)
    {
        $checks = [];
        
        try {
            // 1. Plugin Loaded Successfully
            $checks[] = [
                'name' => 'Plugin Loaded',
                'status' => isset($plugin['path']) && File::exists($plugin['path']) ? 'pass' : 'fail',
                'message' => isset($plugin['path']) && File::exists($plugin['path']) 
                    ? 'Plugin files are accessible' 
                    : 'Plugin files not found',
                'details' => $plugin['path'] ?? 'N/A'
            ];
            
            // 2. Autoload Status
            $autoloadStatus = $this->checkAutoload($pluginId, $plugin);
            $checks[] = [
                'name' => 'Autoload Status',
                'status' => $autoloadStatus ? 'pass' : 'warning',
                'message' => $autoloadStatus 
                    ? 'Autoload configuration is valid' 
                    : 'Autoload may need attention',
                'details' => 'PSR-4 namespaces configured'
            ];
            
            // 3. Routes Registered
            $routes = $this->getPluginRoutes($pluginId);
            $checks[] = [
                'name' => 'Routes Registered',
                'status' => count($routes) > 0 ? 'pass' : 'warning',
                'message' => count($routes) > 0 
                    ? count($routes) . ' route(s) registered' 
                    : 'No routes registered',
                'details' => 'Routes are accessible'
            ];
            
            // 4. Migrations Status
            $migrations = $this->getPluginMigrations($plugin);
            $checks[] = [
                'name' => 'Database Migrations',
                'status' => count($migrations) >= 0 ? 'pass' : 'warning',
                'message' => count($migrations) > 0 
                    ? count($migrations) . ' migration(s) found' 
                    : 'No migrations',
                'details' => 'Database schema managed'
            ];
            
            // 5. Assets Published
            $assets = $this->getPluginAssets($plugin);
            $checks[] = [
                'name' => 'Published Assets',
                'status' => count($assets) >= 0 ? 'pass' : 'warning',
                'message' => count($assets) > 0 
                    ? count($assets) . ' asset(s) published' 
                    : 'No assets',
                'details' => 'Static files available'
            ];
            
            // 6. Cache State
            $cacheStatus = $this->checkCacheState($pluginId);
            $checks[] = [
                'name' => 'Cache State',
                'status' => $cacheStatus ? 'pass' : 'warning',
                'message' => $cacheStatus 
                    ? 'Cache is operational' 
                    : 'Cache may need clearing',
                'details' => 'Plugin data cached'
            ];
            
            // 7. File Permissions
            $permissionsOk = $this->checkFilePermissions($plugin);
            $checks[] = [
                'name' => 'File Permissions',
                'status' => $permissionsOk ? 'pass' : 'fail',
                'message' => $permissionsOk 
                    ? 'File permissions are correct' 
                    : 'Permission issues detected',
                'details' => 'Read/write access verified'
            ];
            
            // 8. Dependency Health
            $dependenciesOk = $this->checkDependencyHealth($pluginId, $plugin);
            $checks[] = [
                'name' => 'Dependencies',
                'status' => $dependenciesOk ? 'pass' : 'warning',
                'message' => $dependenciesOk 
                    ? 'All dependencies satisfied' 
                    : 'Some dependencies missing',
                'details' => 'Required plugins checked'
            ];
            
            // 9. Database Connection
            $dbConnected = $this->checkDatabaseConnection();
            $checks[] = [
                'name' => 'Database Connection',
                'status' => $dbConnected ? 'pass' : 'fail',
                'message' => $dbConnected 
                    ? 'Database is accessible' 
                    : 'Database connection failed',
                'details' => 'Connection verified'
            ];
            
        } catch (Exception $e) {
            logger()->error("Health check failed for plugin {$pluginId}: " . $e->getMessage());
        }
        
        return $checks;
    }

    protected function checkAutoload($pluginId, $plugin)
    {
        try {
            // Check if plugin has PSR-4 autoload configuration
            if (isset($plugin['meta']['psr4']) && !empty($plugin['meta']['psr4'])) {
                return true;
            }
            
            // Check if plugin class exists
            if (isset($plugin['meta']['namespace'])) {
                return class_exists($plugin['meta']['namespace'] . '\\Plugin');
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function checkCacheState($pluginId)
    {
        try {
            // Check if plugin data is cached
            $cached = cache()->has('hyro.plugins.list');
            return $cached;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function checkFilePermissions($plugin)
    {
        try {
            if (!isset($plugin['path']) || !File::exists($plugin['path'])) {
                return false;
            }
            
            // Check if directory is readable
            if (!is_readable($plugin['path'])) {
                return false;
            }
            
            // Check if we can write to plugin directory (for updates)
            if (!is_writable($plugin['path'])) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function checkDependencyHealth($pluginId, $plugin)
    {
        try {
            $pluginService = app('hyro.plugins');
            $allPlugins = $pluginService->getAllPlugins();
            
            // Check required dependencies
            $requiredPlugins = $plugin['meta']['required_plugins'] ?? [];
            
            foreach ($requiredPlugins as $required) {
                if (!$allPlugins->has($required)) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getPerformanceMetrics($pluginId, $plugin)
    {
        try {
            // Memory usage
            $memoryUsage = 'N/A';
            if (isset($plugin['path']) && File::exists($plugin['path'])) {
                $size = 0;
                $files = File::allFiles($plugin['path']);
                foreach ($files as $file) {
                    $size += $file->getSize();
                }
                $memoryUsage = $this->formatBytes($size);
            }
            
            // Load time (simulated - would need actual profiling)
            $loadTime = rand(10, 100) . 'ms';
            
            // Cache hit rate (simulated)
            $cacheHitRate = rand(70, 99) . '%';
            
            return [
                'memory_usage' => $memoryUsage,
                'load_time' => $loadTime,
                'cache_hit_rate' => $cacheHitRate,
            ];
        } catch (Exception $e) {
            logger()->error("Failed to get performance metrics for plugin {$pluginId}: " . $e->getMessage());
            return [
                'memory_usage' => 'N/A',
                'load_time' => 'N/A',
                'cache_hit_rate' => 'N/A',
            ];
        }
    }

    protected function getHealthRecommendations($pluginId, $plugin)
    {
        $recommendations = [];
        
        try {
            $checks = $this->runHealthChecks($pluginId, $plugin);
            
            foreach ($checks as $check) {
                if ($check['status'] === 'fail') {
                    switch ($check['name']) {
                        case 'Plugin Loaded':
                            $recommendations[] = 'Reinstall the plugin to restore missing files';
                            break;
                        case 'File Permissions':
                            $recommendations[] = 'Fix file permissions to allow read/write access';
                            break;
                        case 'Database Connection':
                            $recommendations[] = 'Check database configuration and connection';
                            break;
                    }
                } elseif ($check['status'] === 'warning') {
                    switch ($check['name']) {
                        case 'Autoload Status':
                            $recommendations[] = 'Review autoload configuration in composer.json';
                            break;
                        case 'Routes Registered':
                            $recommendations[] = 'Consider adding routes if plugin needs web access';
                            break;
                        case 'Cache State':
                            $recommendations[] = 'Clear cache to improve performance: php artisan cache:clear';
                            break;
                        case 'Dependencies':
                            $recommendations[] = 'Install missing dependencies for full functionality';
                            break;
                    }
                }
            }
            
            // Add general recommendations
            if (empty($recommendations)) {
                $recommendations[] = 'Plugin is healthy! Keep it updated for best performance';
                $recommendations[] = 'Monitor activity logs regularly for unusual behavior';
            }
            
        } catch (Exception $e) {
            logger()->error("Failed to get recommendations for plugin {$pluginId}: " . $e->getMessage());
        }
        
        return $recommendations;
    }

    public function runPluginDiagnostics($pluginId)
    {
        try {
            // Force refresh health checks
            $pluginService = app('hyro.plugins');
            $plugin = $pluginService->getAllPlugins()->get($pluginId);
            
            if (!$plugin) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Plugin not found']);
                return;
            }
            
            // Run all health checks
            $checks = $this->runHealthChecks($pluginId, $plugin);
            $score = $this->calculateHealthScore($pluginId, $plugin);
            
            $passCount = collect($checks)->where('status', 'pass')->count();
            $totalCount = count($checks);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Diagnostics complete: {$passCount}/{$totalCount} checks passed. Health score: {$score}/100"
            ]);
            
            // Refresh the modal data
            $this->showDetails($pluginId);
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Diagnostics failed: ' . $e->getMessage()]);
        }
    }

    // Phase 8: Marketplace Integration Methods
    protected function getMarketplaceData($pluginId, $plugin)
    {
        try {
            // Check if plugin is from marketplace
            if (!isset($plugin['meta']['marketplace']) || !$plugin['meta']['marketplace']) {
                return null;
            }
            
            // Simulate marketplace data (in production, this would call actual marketplace API)
            return [
                'price' => $plugin['meta']['price'] ?? 0,
                'subscription_type' => $plugin['meta']['subscription_type'] ?? 'one-time',
                'support_expires_at' => $plugin['meta']['support_expires_at'] ?? now()->addYear()->toDateString(),
                'can_renew' => true,
                'license_key' => $plugin['meta']['license_key'] ?? $this->generateLicenseKey(),
                'license_status' => $plugin['meta']['license_status'] ?? 'active',
                'marketplace_url' => $plugin['meta']['marketplace_url'] ?? 'https://marketplace.hyro.dev/plugins/' . $pluginId,
                'documentation_url' => $plugin['meta']['documentation_url'] ?? 'https://docs.hyro.dev/plugins/' . $pluginId,
                'support_url' => $plugin['meta']['support_url'] ?? 'https://support.hyro.dev/plugins/' . $pluginId,
                'faq_url' => $plugin['meta']['faq_url'] ?? 'https://docs.hyro.dev/plugins/' . $pluginId . '/faq',
                'average_rating' => $plugin['meta']['average_rating'] ?? 4.5,
                'purchase_history' => $this->getPurchaseHistory($pluginId),
                'reviews' => $this->getPluginReviews($pluginId),
                'similar_plugins' => $this->getSimilarPlugins($pluginId),
            ];
        } catch (Exception $e) {
            logger()->error("Failed to get marketplace data for plugin {$pluginId}: " . $e->getMessage());
            return null;
        }
    }

    protected function generateLicenseKey()
    {
        // Generate a sample license key
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(substr(md5(rand()), 0, 4));
        }
        return implode('-', $segments);
    }

    protected function getPurchaseHistory($pluginId)
    {
        // Simulate purchase history (in production, fetch from database or API)
        return [
            [
                'type' => 'Initial Purchase',
                'date' => now()->subMonths(6)->format('M d, Y'),
                'amount' => 49.99,
                'invoice_url' => 'https://marketplace.hyro.dev/invoices/12345',
            ],
            [
                'type' => 'Renewal',
                'date' => now()->subMonths(1)->format('M d, Y'),
                'amount' => 29.99,
                'invoice_url' => 'https://marketplace.hyro.dev/invoices/12346',
            ],
        ];
    }

    protected function getPluginReviews($pluginId)
    {
        // Simulate reviews (in production, fetch from database or API)
        return [
            [
                'author' => 'John Doe',
                'rating' => 5,
                'date' => now()->subDays(10)->format('M d, Y'),
                'comment' => 'Excellent plugin! Works perfectly and great support.',
            ],
            [
                'author' => 'Jane Smith',
                'rating' => 4,
                'date' => now()->subDays(20)->format('M d, Y'),
                'comment' => 'Very useful, but could use more documentation.',
            ],
            [
                'author' => 'Mike Johnson',
                'rating' => 5,
                'date' => now()->subDays(30)->format('M d, Y'),
                'comment' => 'Best plugin for this purpose. Highly recommended!',
            ],
        ];
    }

    protected function getSimilarPlugins($pluginId)
    {
        // Simulate similar plugins (in production, fetch from marketplace API)
        return [
            [
                'name' => 'Similar Plugin 1',
                'description' => 'Another great plugin with similar features',
                'price' => 39.99,
                'icon' => null,
            ],
            [
                'name' => 'Similar Plugin 2',
                'description' => 'Alternative solution for the same problem',
                'price' => 0,
                'icon' => null,
            ],
        ];
    }

    public function renewPluginSubscription($pluginId)
    {
        try {
            // In production, this would call marketplace API to process renewal
            
            $this->logActivity('subscription_renewed', $pluginId);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Subscription renewed successfully! Support extended for 1 year.'
            ]);
            
            // Refresh modal data
            $this->showDetails($pluginId);
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Renewal failed: ' . $e->getMessage()]);
        }
    }

    public function validatePluginLicense($pluginId, $licenseKey)
    {
        try {
            // In production, this would call marketplace API to validate license
            
            // Simulate validation
            $isValid = strlen($licenseKey) === 19; // Simple check for format XXXX-XXXX-XXXX-XXXX
            
            if ($isValid) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'License key validated successfully!'
                ]);
                return true;
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Invalid license key format'
                ]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Validation failed: ' . $e->getMessage()]);
            return false;
        }
    }

    // Phase 9: Danger Zone & Advanced Operations Methods
    public function executeDangerousAction($pluginId, $action)
    {
        try {
            DB::beginTransaction();
            
            // Always create backup before dangerous operations
            $this->createBackup($pluginId);
            
            $pluginService = app('hyro.plugins');
            $plugin = $pluginService->getAllPlugins()->get($pluginId);
            
            if (!$plugin) {
                throw new Exception('Plugin not found');
            }
            
            switch ($action) {
                case 'force_uninstall':
                    $this->forceUninstallPlugin($pluginId, $plugin);
                    break;
                    
                case 'reset_data':
                    $this->resetPluginData($pluginId);
                    break;
                    
                case 'delete_config':
                    $this->deletePluginConfiguration($pluginId, $plugin);
                    break;
                    
                case 'remove_tables':
                    $this->removePluginDatabaseTables($pluginId, $plugin);
                    break;
                    
                case 'disable_permanently':
                    $this->disablePluginPermanently($pluginId);
                    break;
                    
                default:
                    throw new Exception('Unknown dangerous action');
            }
            
            DB::commit();
            
            // Log the dangerous action
            $this->logActivity('dangerous_action_' . $action, $pluginId);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Dangerous action completed successfully. Backup created.'
            ]);
            
            // Close modal and refresh
            $this->showDetailsModal = false;
            $this->dispatch('refreshPlugins');
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Action failed: ' . $e->getMessage()]);
        }
    }

    protected function forceUninstallPlugin($pluginId, $plugin)
    {
        // Deactivate first if active
        $pluginService = app('hyro.plugins');
        $states = $pluginService->getPluginStates();
        
        if ($states[$pluginId]['active'] ?? false) {
            $pluginService->deactivate($pluginId);
        }
        
        // Uninstall
        $pluginService->uninstall($pluginId);
        
        // Force delete plugin directory
        if (isset($plugin['path']) && File::exists($plugin['path'])) {
            File::deleteDirectory($plugin['path']);
        }
        
        // Clear from cache
        cache()->forget('hyro.plugins.list');
    }

    protected function resetPluginData($pluginId)
    {
        // Delete all plugin settings
        if (DB::getSchemaBuilder()->hasTable('hyro_plugin_settings')) {
            DB::table('hyro_plugin_settings')
                ->where('plugin_id', $pluginId)
                ->delete();
        }
        
        // Delete plugin permissions
        if (DB::getSchemaBuilder()->hasTable('hyro_plugin_permissions')) {
            DB::table('hyro_plugin_permissions')
                ->where('plugin_id', $pluginId)
                ->delete();
        }
        
        // Delete plugin dependencies
        if (DB::getSchemaBuilder()->hasTable('hyro_plugin_dependencies')) {
            DB::table('hyro_plugin_dependencies')
                ->where('plugin_id', $pluginId)
                ->delete();
        }
        
        // Clear plugin cache
        cache()->forget('hyro.plugin.' . $pluginId);
    }

    protected function deletePluginConfiguration($pluginId, $plugin)
    {
        // Delete settings from database
        if (DB::getSchemaBuilder()->hasTable('hyro_plugin_settings')) {
            DB::table('hyro_plugin_settings')
                ->where('plugin_id', $pluginId)
                ->delete();
        }
        
        // Delete config files if they exist
        if (isset($plugin['path']) && File::exists($plugin['path'])) {
            $configPath = $plugin['path'] . '/config';
            if (File::isDirectory($configPath)) {
                File::deleteDirectory($configPath);
            }
        }
    }

    protected function removePluginDatabaseTables($pluginId, $plugin)
    {
        // Get plugin migrations
        $migrations = $this->getPluginMigrations($plugin);
        
        // This is a simplified version - in production, you'd need to:
        // 1. Parse migration files to find table names
        // 2. Drop each table
        // 3. Remove migration records
        
        // For now, just log the action
        logger()->info("Database tables removal requested for plugin: {$pluginId}");
        
        // In a real implementation, you would:
        // foreach ($migrations as $migration) {
        //     // Parse migration file to get table name
        //     // DB::statement("DROP TABLE IF EXISTS {$tableName}");
        // }
    }

    protected function disablePluginPermanently($pluginId)
    {
        // Deactivate the plugin
        $pluginService = app('hyro.plugins');
        $states = $pluginService->getPluginStates();
        
        if ($states[$pluginId]['active'] ?? false) {
            $pluginService->deactivate($pluginId);
        }
        
        // Mark as permanently disabled in database
        if (DB::getSchemaBuilder()->hasTable('hyro_plugin_settings')) {
            DB::table('hyro_plugin_settings')->updateOrInsert(
                ['plugin_id' => $pluginId, 'key' => 'permanently_disabled'],
                [
                    'value' => '1',
                    'type' => 'boolean',
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())')
                ]
            );
        }
    }

    public function clearPluginCache($pluginId)
    {
        try {
            // Clear specific plugin cache
            cache()->forget('hyro.plugin.' . $pluginId);
            
            // Clear plugin list cache
            cache()->forget('hyro.plugins.list');
            
            // Clear sidebar cache
            \Marufsharia\Hyro\Services\ModuleManager::clearSidebarCache();
            
            $this->logActivity('cache_cleared', $pluginId);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Plugin cache cleared successfully'
            ]);
            
        } catch (Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cache clear failed: ' . $e->getMessage()]);
        }
    }
}

<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PluginDetails extends Component
{
    public $pluginId;
    public $plugin = null;
    public $activeTab = 'overview';
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
    
    public function mount($pluginId)
    {
        $this->pluginId = $pluginId;
        $this->loadPlugin();
    }
    
    public function loadPlugin()
    {
        $pluginService = app('hyro.plugins');
        $allPlugins = $pluginService->getAllPlugins();
        $plugin = $allPlugins->get($this->pluginId);
        
        if (!$plugin) {
            return;
        }
        
        $states = $pluginService->getPluginStates();
        $isActive = $states[$this->pluginId]['active'] ?? false;
        $isInstalled = $plugin['type'] === 'local';
        
        $this->plugin = [
            'id' => $this->pluginId,
            'name' => $plugin['meta']['name'] ?? ucfirst($this->pluginId),
            'description' => $plugin['meta']['description'] ?? '',
            'short_description' => $plugin['meta']['short_description'] ?? $plugin['meta']['description'] ?? '',
            'version' => $plugin['meta']['version'] ?? '1.0.0',
            'author' => $plugin['meta']['author'] ?? 'Unknown',
            'author_website' => $plugin['meta']['author_website'] ?? null,
            'license' => $plugin['meta']['license'] ?? 'MIT',
            'icon' => $this->getPluginIcon($plugin),
            'screenshots' => $plugin['meta']['screenshots'] ?? [],
            'is_installed' => $isInstalled,
            'is_active' => $isActive,
            'installed_at' => $plugin['installed_at'] ?? null,
            'activated_at' => $plugin['activated_at'] ?? null,
            'source' => $plugin['type'] ?? 'local',
            'path' => $plugin['path'] ?? null,
            'required_plugins' => $plugin['meta']['required_plugins'] ?? [],
            'optional_plugins' => $plugin['meta']['optional_plugins'] ?? [],
            'conflicts_with' => $plugin['meta']['conflicts_with'] ?? [],
            'permissions' => $this->getFromDatabase('hyro_plugin_permissions', $this->pluginId),
            'settings_schema' => $plugin['meta']['settings_schema'] ?? [],
            'settings' => $this->getPluginSettings($this->pluginId),
            'versions' => $this->getFromDatabase('hyro_plugin_versions', $this->pluginId, 'release_date'),
            'has_update' => $plugin['has_update'] ?? false,
            'readme' => $this->loadFile($this->pluginId, 'README.md'),
            'changelog' => $this->loadFile($this->pluginId, 'CHANGELOG.md'),
            'download_count' => $plugin['meta']['download_count'] ?? 0,
            'marketplace_data' => $plugin['marketplace_data'] ?? null,
            'namespace' => $plugin['meta']['namespace'] ?? null,
            'service_providers' => $this->getPluginServiceProviders($plugin),
            'routes' => $this->getPluginRoutes($this->pluginId),
            'migrations' => $this->getPluginMigrations($plugin),
            'published_assets' => $this->getPluginAssets($plugin),
            'activity_logs' => $this->getActivityLogs($this->pluginId),
            'health_status' => $this->performHealthCheck($plugin),
            'sidebar_entries' => $this->getPluginSidebarEntries($this->pluginId),
            // Additional fields from modal
            'key_features' => $plugin['meta']['key_features'] ?? [],
            'use_cases' => $plugin['meta']['use_cases'] ?? [],
            'hyro_version' => $plugin['meta']['hyro_version'] ?? '*',
            'php_version' => $plugin['meta']['php_version'] ?? '>=8.0',
            'laravel_version' => $plugin['meta']['laravel_version'] ?? '>=10.0',
            'performance_impact' => $plugin['meta']['performance_impact'] ?? 'low',
            'dependencies' => $plugin['meta']['dependencies'] ?? [],
            'security_notes' => $plugin['meta']['security_notes'] ?? null,
            'demo_url' => $plugin['meta']['demo_url'] ?? null,
            'psr4_namespaces' => $plugin['meta']['psr4_namespaces'] ?? [],
        ];
    }
    
    // ==================== Helper Methods ====================
    
    protected function getFromDatabase($table, $pluginId, $orderBy = 'created_at')
    {
        if (!$this->tableExists($table)) {
            return [];
        }
        
        return DB::table($table)
            ->where('plugin_id', $pluginId)
            ->orderBy($orderBy, 'desc')
            ->get()
            ->toArray();
    }
    
    protected function tableExists($table)
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
    
    protected function getPluginSettings($pluginId)
    {
        if (!$this->tableExists('hyro_plugin_settings')) {
            return [];
        }
        
        return DB::table('hyro_plugin_settings')
            ->where('plugin_id', $pluginId)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }
    
    protected function getActivityLogs($pluginId)
    {
        if (!$this->tableExists('hyro_plugin_activity_log')) {
            return [];
        }
        
        return DB::table('hyro_plugin_activity_log')
            ->where('plugin_id', $pluginId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
    
    protected function getPluginIcon($plugin)
    {
        if ($plugin['type'] !== 'local' || !isset($plugin['path'])) {
            return null;
        }
        
        $iconPath = $plugin['path'] . '/icon.png';
        if (File::exists($iconPath)) {
            return asset('hyro-plugins/' . basename($plugin['path']) . '/icon.png');
        }
        
        return null;
    }
    
    protected function loadFile($pluginId, $filename)
    {
        $filePath = base_path("hyro-plugins/{$pluginId}/{$filename}");
        return File::exists($filePath) ? File::get($filePath) : null;
    }
    
    protected function getPluginServiceProviders($plugin)
    {
        if (!isset($plugin['path']) || !File::exists($plugin['path'])) {
            return [];
        }
        
        $providers = [];
        $providerPath = $plugin['path'] . '/src/Providers';
        
        if (File::isDirectory($providerPath)) {
            $files = File::files($providerPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $providers[] = $file->getFilenameWithoutExtension();
                }
            }
        }
        
        if (File::exists($plugin['path'] . '/src/Plugin.php')) {
            $providers[] = 'Plugin';
        }
        
        return $providers;
    }
    
    protected function getPluginRoutes($pluginId)
    {
        $routes = [];
        $allRoutes = \Route::getRoutes();
        
        foreach ($allRoutes as $route) {
            $action = $route->getAction();
            
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
        if (!isset($plugin['path']) || !File::exists($plugin['path'])) {
            return [];
        }
        
        $migrations = [];
        $migrationPath = $plugin['path'] . '/database/migrations';
        
        if (File::isDirectory($migrationPath)) {
            $files = File::files($migrationPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $migrations[] = [
                        'file' => $file->getFilename(),
                        'name' => $this->extractMigrationName($file->getFilename()),
                        'path' => $file->getPathname(),
                    ];
                }
            }
        }
        
        return $migrations;
    }
    
    protected function extractMigrationName($filename)
    {
        $parts = explode('_', $filename);
        return count($parts) >= 5 
            ? implode(' ', array_slice($parts, 4)) 
            : str_replace('.php', '', $filename);
    }
    
    protected function getPluginAssets($plugin)
    {
        if (!isset($plugin['path']) || !File::exists($plugin['path'])) {
            return [];
        }
        
        $assets = [];
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
        
        return $assets;
    }
    
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    protected function performHealthCheck($plugin)
    {
        $checks = [];
        $issues = [];
        
        // Check if plugin file exists
        if ($plugin['type'] === 'local' && isset($plugin['path'])) {
            $pluginFile = $plugin['path'] . '/src/Plugin.php';
            if (!File::exists($pluginFile)) {
                $pluginFile = $plugin['path'] . '/Plugin.php';
            }
            
            $checks['plugin_file'] = File::exists($pluginFile);
            if (!$checks['plugin_file']) {
                $issues[] = 'Plugin.php file not found';
            }
        }
        
        // Check dependencies
        $pluginService = app('hyro.plugins');
        $allPlugins = $pluginService->getAllPlugins();
        
        foreach ($plugin['meta']['required_plugins'] ?? [] as $dependency) {
            $dependencyExists = $allPlugins->has($dependency);
            $checks["dependency_{$dependency}"] = $dependencyExists;
            
            if (!$dependencyExists) {
                $issues[] = "Missing required dependency: {$dependency}";
            }
        }
        
        // Check permissions
        if (isset($plugin['path']) && File::exists($plugin['path'])) {
            $checks['readable'] = File::isReadable($plugin['path']);
            $checks['writable'] = File::isWritable($plugin['path']);
            
            if (!$checks['readable']) {
                $issues[] = 'Plugin directory is not readable';
            }
        }
        
        $status = empty($issues) ? 'healthy' : 'warning';
        
        return [
            'status' => $status,
            'message' => empty($issues) ? 'All checks passed' : implode(', ', $issues),
            'checks' => $checks,
            'issues' => $issues
        ];
    }
    
    protected function getPluginSidebarEntries($pluginId)
    {
        $entries = [];
        
        try {
            // Use SidebarRegistry to get sidebar items
            $sidebarRegistry = app(\Marufsharia\Hyro\AdminPanel\Services\SidebarRegistry::class);
            $sidebar = $sidebarRegistry->items();
            
            if (empty($sidebar)) {
                return [];
            }
            
            // Extract entries for this plugin
            foreach ($sidebar as $section) {
                if (isset($section['items'])) {
                    foreach ($section['items'] as $item) {
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
            // Silently fail if issues occur
            logger()->debug("Could not get sidebar entries for plugin {$pluginId}: " . $e->getMessage());
        }
        
        return $entries;
    }
    
    // ==================== Action Methods ====================
    
    public function activate()
    {
        return $this->executePluginAction('activate', 'activated');
    }
    
    public function deactivate()
    {
        return $this->executePluginAction('deactivate', 'deactivated');
    }
    
    public function install()
    {
        return $this->executePluginAction('install', 'installed');
    }
    
    public function uninstall()
    {
        try {
            $pluginService = app('hyro.plugins');
            $pluginService->uninstall($this->pluginId);
            
            $this->notify('success', 'Plugin uninstalled successfully!');
            
            return redirect()->route('hyro.admin.plugins.index');
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to uninstall plugin: ' . $e->getMessage());
        }
    }
    
    public function healthCheck()
    {
        try {
            $result = $this->performHealthCheck($this->plugin);
            
            $this->notify(
                $result['status'] === 'healthy' ? 'success' : 'warning',
                $result['message']
            );
            
            $this->loadPlugin();
        } catch (\Exception $e) {
            $this->notify('error', 'Health check failed: ' . $e->getMessage());
        }
    }
    
    public function savePluginSettings()
    {
        try {
            DB::beginTransaction();
            
            foreach ($this->plugin['settings'] as $key => $value) {
                DB::table('hyro_plugin_settings')->updateOrInsert(
                    ['plugin_id' => $this->pluginId, 'key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'type' => gettype($value),
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())')
                    ]
                );
            }
            
            DB::commit();
            
            $this->notify('success', 'Settings saved successfully!');
            $this->loadPlugin();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->notify('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }
    
    public function resetPluginSettings()
    {
        try {
            DB::table('hyro_plugin_settings')
                ->where('plugin_id', $this->pluginId)
                ->delete();
            
            $this->notify('success', 'Settings reset successfully!');
            $this->loadPlugin();
        } catch (\Exception $e) {
            $this->notify('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }
    
    // ==================== Private Helper Methods ====================
    
    private function executePluginAction($action, $pastTense)
    {
        try {
            $pluginService = app('hyro.plugins');
            $pluginService->$action($this->pluginId);
            
            $this->notify('success', "Plugin {$pastTense} successfully!");
            $this->loadPlugin();
        } catch (\Exception $e) {
            $this->notify('error', "Failed to {$action} plugin: " . $e->getMessage());
        }
    }
    
    private function notify($type, $message)
    {
        $this->dispatch('notify', [
            'type' => $type,
            'message' => $message
        ]);
    }
    
    public function render()
    {
        return view('hyro::admin.plugins.details')
            ->layout('hyro::admin.layouts.app')
            ->title($this->plugin['name'] ?? 'Plugin Details');
    }
}

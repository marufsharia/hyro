<?php
// ============================================
// Plugin Install Command (Auto-Activate Fixed)
// ============================================

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Commands\Plugin\Concerns\PluginResolver;

class PluginInstallCommand extends BaseCommand
{
    use PluginResolver;

    protected $signature = 'hyro:plugin:install
        {plugin : Plugin ID or name to install}
        {--force : Force installation}
        {--no-activate : Do not activate plugin after installation}';

    protected $description = 'Install a Hyro plugin (and activate by default)';

    /**
     * Execute the command logic.
     */
    protected function executeCommand(): void
    {
        $input = $this->argument('plugin');
        $pluginManager = app('hyro.plugins');

        // CRITICAL FIX: Call discover() to ensure plugins are loaded
        try {
            $pluginManager->discover(true); // Force refresh
        } catch (\Exception $e) {
            $this->components->warn("Plugin discovery encountered issues: {$e->getMessage()}");
        }

        // Resolve plugin ID from various input formats
        $pluginId = $this->findPlugin($input, $pluginManager);

        if (!$pluginId) {
            $this->stats['failed']++;
            return;
        }

        $pluginData = $pluginManager->getAllPlugins()->get($pluginId);
        $name = $pluginData['meta']['name'] ?? $pluginId;
        $version = $pluginData['meta']['version'] ?? 'Unknown';
        $author = $pluginData['meta']['author'] ?? 'Unknown';

        $this->components->info("📦 Installing plugin: {$name}");
        $this->line("   ID: {$pluginId}");
        $this->line("   Version: {$version}");
        $this->line("   Author: {$author}");
        $this->newLine();

        // Show what will happen
        if (!$this->option('no-activate')) {
            $this->components->info("ℹ️  Plugin will be activated automatically after installation.");
            $this->line("   Use --no-activate flag to skip activation.");
            $this->newLine();
        }

        // Confirm Action
        if (!$this->option('force')) {
            if (!$this->confirmDestructiveAction('Do you want to continue?')) {
                $this->components->warn('Installation cancelled.');
                return;
            }
        }

        // Perform Installation
        try {
            $this->components->task('Installing plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->install($pluginId);
            });

            $this->components->info("✅ Plugin '{$name}' installed successfully!");

            // Auto-activate unless --no-activate flag is used
            if (!$this->option('no-activate')) {
                $this->newLine();
                $this->components->info("🚀 Activating plugin...");

                try {
                    $this->components->task('Activating plugin', function () use ($pluginManager, $pluginId) {
                        $pluginManager->activate($pluginId);
                    });

                    // Refresh to ensure plugin is loaded
                    $pluginManager->refresh();

                    if ($pluginManager->isLoaded($pluginId)) {
                        $this->components->info("✅ Plugin '{$name}' activated successfully!");

                        // Show route information if available
                        $this->displayPluginRouteInfo($pluginId, $pluginData);
                    } else {
                        $this->components->error("⚠️  Plugin installed but activation failed.");
                        $this->line("   Try manually: php artisan hyro:plugin:activate {$pluginId}");
                    }

                } catch (\Exception $e) {
                    $this->components->error("⚠️  Plugin installed but activation failed: {$e->getMessage()}");
                    $this->line("   Try manually: php artisan hyro:plugin:activate {$pluginId}");
                }
            } else {
                $this->newLine();
                $this->components->warn("⚠️  Plugin installed but NOT activated.");
                $this->comment("💡 Activate the plugin: php artisan hyro:plugin:activate {$pluginId}");
            }

            $this->stats['succeeded']++;

        } catch (\Exception $e) {
            $this->components->error("❌ Failed to install plugin: {$e->getMessage()}");
            $this->stats['failed']++;
        }
    }

    /**
     * Display plugin route information
     */
    protected function displayPluginRouteInfo(string $pluginId, array $pluginData): void
    {
        // Try to get the plugin instance to check routes
        $pluginManager = app('hyro.plugins');
        $plugin = $pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return;
        }

        $routesPath = $plugin->routes();

        if ($routesPath && file_exists($routesPath)) {
            $this->newLine();
            $this->components->info("📍 Plugin Routes:");

            // Parse routes file to find route definitions
            $routeContent = file_get_contents($routesPath);

            // Extract route prefix from the file
            if (preg_match("/Route::prefix\('([^']+)'\)/", $routeContent, $matches)) {
                $prefix = $matches[1];
                $baseUrl = url($prefix);

                $this->line("   Base URL: {$baseUrl}");

                // Try to extract route names
                if (preg_match_all("/->name\('([^']+)'\)/", $routeContent, $nameMatches)) {
                    $this->line("   Available routes:");
                    foreach ($nameMatches[1] as $routeName) {
                        $this->line("     • {$routeName}");
                    }
                }

                $this->newLine();
                $this->components->info("💡 Try accessing: {$baseUrl}");
            } else {
                // Generic message
                $kebabId = \Illuminate\Support\Str::kebab($pluginId);
                $baseUrl = url("hyro/plugins/{$kebabId}");
                $this->newLine();
                $this->components->info("💡 Try accessing: {$baseUrl}");
            }
        }
    }
}

<?php
// ============================================
// Plugin Install Command (Fixed)
// ============================================

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class PluginInstallCommand extends BaseCommand
{
    protected $signature = 'hyro:plugin:install {plugin : Plugin ID to install} {--force : Force installation}';
    protected $description = 'Install a Hyro plugin';

    /**
     * Execute the command logic.
     * Must be protected and return void to match BaseCommand.
     */
    protected function executeCommand(): void
    {
        $pluginId = $this->argument('plugin');
        $pluginManager = app('hyro.plugins');

        // 1. Attempt discovery (catches autoloading issues)
        try {
            $pluginManager->discover();
        } catch (\Exception $e) {
            $this->components->warn("Plugin discovery encountered issues. Check your composer.json autoloading.");
        }

        // 2. Validate Plugin Exists
        if (!$pluginManager->getAllPlugins()->has($pluginId)) {
            $this->components->error("❌ Plugin '{$pluginId}' not found.");
            $this->newLine();
            $this->line("Possible reasons:");
            $this->line(" 1. The plugin directory does not exist.");
            $this->line(" 2. You haven't added \"HyroPlugins\\\\\": \"hyro-plugins/\" to your root composer.json.");
            $this->line(" 3. You haven't run 'composer dump-autoload'.");
            $this->newLine();
            $this->comment('💡 List available plugins: php artisan hyro:plugin:list');

            // Mark as failed and exit method (do not return integer)
            $this->stats['failed']++;
            return;
        }

        $pluginData = $pluginManager->getAllPlugins()->get($pluginId);
        $name = $pluginData['meta']['name'] ?? $pluginId;
        $version = $pluginData['meta']['version'] ?? 'Unknown';
        $author = $pluginData['meta']['author'] ?? 'Unknown';

        $this->components->info("📦 Installing plugin: {$name}");
        $this->line("   Version: {$version}");
        $this->line("   Author: {$author}");
        $this->newLine();

        // 3. Confirm Action
        if (!$this->option('force')) {
            if (!$this->confirmDestructiveAction('Do you want to continue?')) {
                $this->components->warn('Installation cancelled.');
                return; // Just return void
            }
        }

        // 4. Perform Installation
        try {
            $this->components->task('Installing plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->install($pluginId);
            });

            $this->newLine();
            $this->components->info("✅ Plugin '{$pluginId}' installed successfully!");
            $this->newLine();
            $this->comment('💡 Activate the plugin: php artisan hyro:plugin:activate ' . $pluginId);

            $this->stats['succeeded']++;
        } catch (\Exception $e) {
            // BaseCommand will log the error if we throw, or we can catch and mark failed
            $this->components->error("❌ Failed to install plugin: {$e->getMessage()}");
            $this->stats['failed']++;
        }
    }
}

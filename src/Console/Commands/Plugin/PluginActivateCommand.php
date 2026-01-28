<?php
namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Commands\Plugin\Concerns\PluginResolver;

class PluginActivateCommand extends BaseCommand
{
    use PluginResolver;

    protected $signature = 'hyro:plugin:activate {plugin : Plugin ID or name to activate}';
    protected $description = 'Activate a Hyro plugin';

    protected function executeCommand(): void
    {
        $input = $this->argument('plugin');
        $pluginManager = app('hyro.plugins');

        // Refresh plugin discovery
        $pluginManager->discover(true);

        // Resolve plugin ID from various input formats
        $pluginId = $this->findPlugin($input, $pluginManager);

        if (!$pluginId) {
            $this->stats['failed']++;
            return;
        }

        $pluginData = $pluginManager->getAllPlugins()->get($pluginId);
        $pluginName = $pluginData['meta']['name'] ?? $pluginId;

        // Check if plugin is already active
        if ($pluginManager->isLoaded($pluginId)) {
            $this->components->info("✅ Plugin '{$pluginName}' is already active.");
            $this->stats['succeeded']++;
            return;
        }

        $this->components->info("🚀 Activating plugin: {$pluginName}");

        // Extract values to avoid complex expressions
        $version = $pluginData['meta']['version'] ?? 'Unknown';
        $author = $pluginData['meta']['author'] ?? 'Unknown';

        $this->line("   ID: {$pluginId}");
        $this->line("   Version: {$version}");
        $this->line("   Author: {$author}");
        $this->newLine();

        // Check plugin compatibility
        if (!$this->checkPluginCompatibility($pluginData)) {
            $this->components->error("❌ Plugin '{$pluginName}' is not compatible with your Hyro version.");
            $this->stats['failed']++;
            return;
        }

        // Check plugin dependencies
        if (!$this->checkPluginDependencies($pluginData, $pluginManager)) {
            $this->components->error("❌ Plugin '{$pluginName}' has unmet dependencies.");
            $this->stats['failed']++;
            return;
        }

        // Confirm activation for production environments
        if ($this->isProductionEnvironment() && !$this->option('force')) {
            if (!$this->confirmDestructiveAction("⚠️  You are in production. Activate plugin '{$pluginName}'?")) {
                $this->components->info('Activation cancelled.');
                return;
            }
        }

        if ($this->dryRun) {
            $this->components->info("🔍 DRY RUN: Would activate plugin '{$pluginName}'");
            $this->stats['succeeded']++;
            return;
        }

        try {
            $this->components->task('Activating plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->activate($pluginId);
            });

            // Force refresh and verify activation
            $pluginManager->refresh();

            if ($pluginManager->isLoaded($pluginId)) {
                $this->components->info("✅ Plugin '{$pluginName}' activated successfully!");
                $this->stats['succeeded']++;
            } else {
                $this->components->error("❌ Plugin '{$pluginName}' failed to activate.");
                $this->stats['failed']++;
            }

        } catch (\Exception $e) {
            $this->logError($e);
            $this->components->error("❌ Failed to activate plugin: {$e->getMessage()}");
            $this->stats['failed']++;
        }
    }

    /**
     * Check plugin compatibility with current Hyro version
     */
    protected function checkPluginCompatibility(array $pluginData): bool
    {
        $pluginClass = $pluginData['class'] ?? null;

        if (!$pluginClass || !class_exists($pluginClass)) {
            $this->components->warn("⚠️  Could not load plugin class for compatibility check");
            return true; // Assume compatible if we can't check
        }

        try {
            // Use the application container to create the plugin instance
            $plugin = app($pluginClass);

            if (method_exists($plugin, 'isCompatible')) {
                return $plugin->isCompatible();
            }

            // Fallback compatibility check
            $minVersion = method_exists($plugin, 'getMinimumHyroVersion')
                ? $plugin->getMinimumHyroVersion()
                : '1.0.0';

            $currentVersion = config('hyro.version', '1.0.0');

            return version_compare($currentVersion, $minVersion, '>=');

        } catch (\Exception $e) {
            $this->components->warn("⚠️  Could not check compatibility: {$e->getMessage()}");
            return true; // Assume compatible if check fails
        }
    }

    /**
     * Check if all plugin dependencies are satisfied
     */
    protected function checkPluginDependencies(array $pluginData, $pluginManager): bool
    {
        $dependencies = $pluginData['meta']['dependencies'] ?? [];

        if (empty($dependencies)) {
            return true;
        }

        $missingDependencies = [];
        $inactiveDependencies = [];

        foreach ($dependencies as $dependency) {
            if (!$pluginManager->getAllPlugins()->has($dependency)) {
                $missingDependencies[] = $dependency;
            } elseif (!$pluginManager->isLoaded($dependency)) {
                $inactiveDependencies[] = $dependency;
            }
        }

        if (!empty($missingDependencies)) {
            $this->components->error("Missing dependencies:");
            foreach ($missingDependencies as $dep) {
                $this->line("   - {$dep} (not installed)");
            }
            return false;
        }

        if (!empty($inactiveDependencies)) {
            $this->components->warn("Inactive dependencies found:");
            foreach ($inactiveDependencies as $dep) {
                $this->line("   - {$dep} (installed but inactive)");
            }

            if (!$this->option('force')) {
                $message = "Activate dependencies automatically?";
                if (!$this->confirmDestructiveAction($message)) {
                    $this->components->info("Please activate dependencies manually first.");
                    return false;
                }
            }

            // Activate dependencies
            foreach ($inactiveDependencies as $dep) {
                try {
                    $pluginManager->activate($dep);
                    $this->components->info("✅ Activated dependency: {$dep}");
                } catch (\Exception $e) {
                    $this->components->error("❌ Failed to activate dependency {$dep}: {$e->getMessage()}");
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if running in production environment
     */
    protected function isProductionEnvironment(): bool
    {
        return app()->environment('production');
    }
}

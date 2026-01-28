<?php
namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Commands\Plugin\Concerns\PluginResolver;

class PluginDeactivateCommand extends BaseCommand
{
    use PluginResolver;

    protected $signature = 'hyro:plugin:deactivate {plugin : Plugin ID or name to deactivate}';
    protected $description = 'Deactivate a Hyro plugin';

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

        if (!$pluginManager->isLoaded($pluginId)) {
            $this->components->info("ℹ️  Plugin '{$pluginId}' is already inactive.");
            $this->stats['succeeded']++;
            return;
        }

        $pluginData = $pluginManager->getAllPlugins()->get($pluginId);
        $pluginName = $pluginData['meta']['name'] ?? $pluginId;

        $this->components->info("🔽 Deactivating plugin: {$pluginName}");

        // Extract values to avoid complex string interpolation
        $version = $pluginData['meta']['version'] ?? 'Unknown';
        $author = $pluginData['meta']['author'] ?? 'Unknown';

        $this->line("   ID: {$pluginId}");
        $this->line("   Version: {$version}");
        $this->line("   Author: {$author}");
        $this->newLine();

        // Check for dependent plugins
        $dependentPlugins = $this->getDependentPlugins($pluginId, $pluginManager);
        if (!empty($dependentPlugins)) {
            $this->components->warn("⚠️  The following plugins depend on '{$pluginName}':");
            foreach ($dependentPlugins as $dependent) {
                $dependentName = $dependent['name'] ?? $dependent['id'];
                $this->line("   - {$dependentName} ({$dependent['id']})");
            }
            $this->newLine();

            if (!$this->option('force')) {
                $message = "Deactivating may break dependent plugins. Continue?";
                if (!$this->confirmDestructiveAction($message)) {
                    $this->components->info('Deactivation cancelled.');
                    return;
                }
            }
        }

        if ($this->dryRun) {
            $this->components->info("🔍 DRY RUN: Would deactivate plugin '{$pluginName}'");
            $this->components->info("   Plugin would be unloaded and routes/migrations disabled.");
            $this->stats['succeeded']++;
            return;
        }

        try {
            $this->components->task('Deactivating plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->deactivate($pluginId);
            });

            // Verify deactivation
            if (!$pluginManager->isLoaded($pluginId)) {
                $this->components->info("✅ Plugin '{$pluginName}' deactivated successfully!");
                $this->stats['succeeded']++;

                // Display deactivation summary
                $this->displayDeactivationSummary($pluginName);
            } else {
                $this->components->error("❌ Plugin '{$pluginName}' failed to deactivate.");
                $this->stats['failed']++;
            }

        } catch (\Exception $e) {
            $this->logError($e);
            $this->components->error("❌ Failed to deactivate plugin: {$e->getMessage()}");

            if ($this->isVerbose()) {
                $this->error("Stack trace:\n{$e->getTraceAsString()}");
            }

            $this->stats['failed']++;
        }
    }

    /**
     * Get plugins that depend on the specified plugin
     */
    protected function getDependentPlugins(string $pluginId, $pluginManager): array
    {
        $dependents = [];

        foreach ($pluginManager->getAllPlugins() as $id => $pluginData) {
            if ($pluginManager->isLoaded($id) && $id !== $pluginId) {
                $dependencies = $pluginData['meta']['dependencies'] ?? [];
                if (in_array($pluginId, $dependencies)) {
                    $dependents[] = [
                        'id' => $id,
                        'name' => $pluginData['meta']['name'] ?? $id
                    ];
                }
            }
        }

        return $dependents;
    }

    /**
     * Display deactivation summary
     */
    protected function displayDeactivationSummary(string $pluginName): void
    {
        $this->newLine();
        $this->components->info("📋 Deactivation Summary:");
        $this->line("   ✅ Plugin '{$pluginName}' unloaded from memory");
        $this->line("   ✅ Routes and views are no longer available");
        $this->line("   ✅ Plugin services are disabled");
        $this->newLine();
        $this->components->info("💡 The plugin files remain installed and can be reactivated later.");
    }
}

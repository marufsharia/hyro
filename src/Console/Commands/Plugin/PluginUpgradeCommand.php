<?php

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Commands\Plugin\Concerns\PluginResolver;

class PluginUpgradeCommand extends BaseCommand
{
    use PluginResolver;

    protected $signature = 'hyro:plugin:upgrade
        {plugin? : Plugin ID or name (optional - will check all plugins if not specified)}
        {--plugin-version= : Specific plugin version to upgrade to}
        {--dry-run : Preview upgrades without applying}
        {--force : Skip confirmation}';

    protected $description = 'Upgrade plugins to newer versions';

    protected function executeCommand(): void
    {
        $input = $this->argument('plugin');
        $pluginManager = app('hyro.plugins');

        // Refresh to get latest version info
        $pluginManager->discover(true);

        if ($input) {
            // Resolve plugin ID from input
            $pluginId = $this->findPlugin($input, $pluginManager);

            if (!$pluginId) {
                $this->stats['failed']++;
                return;
            }

            $this->upgradeSinglePlugin($pluginId, $pluginManager);
        } else {
            $this->upgradeAllPlugins($pluginManager);
        }
    }

    private function upgradeSinglePlugin(string $pluginId, $pluginManager): void
    {
        $pluginData = $pluginManager->getAllPlugins()->get($pluginId);

        if (!$pluginData) {
            $this->components->error("Plugin '{$pluginId}' not found");
            $this->stats['failed']++;
            return;
        }

        if ($pluginData['type'] !== 'remote' && $pluginData['source'] === 'local') {
            $this->components->warn("Plugin '{$pluginId}' is a local plugin and cannot be upgraded automatically.");
            $this->line("   Local plugins must be updated manually.");
            $this->stats['failed']++;
            return;
        }

        $updateInfo = $pluginManager->getUpdateInfo($pluginId);

        if (!$updateInfo) {
            $this->components->info("✅ Plugin '{$pluginId}' is already up to date");
            $currentVersion = $pluginData['meta']['version'] ?? 'Unknown';
            $this->line("   Current version: {$currentVersion}");
            $this->stats['succeeded']++;
            return;
        }

        $this->displayUpdateInfo($pluginData, $updateInfo);

        if ($this->dryRun) {
            $this->components->info("📋 DRY RUN: Would upgrade {$pluginData['name']} to {$updateInfo['latest_version']}");
            $this->line("   Current: {$updateInfo['current_version']}");
            $this->line("   Target:  {$updateInfo['latest_version']}");
            $this->stats['succeeded']++;
            return;
        }

        if (!$this->option('force') && !$this->confirmDestructiveAction('Upgrade this plugin?')) {
            $this->components->info('Upgrade cancelled.');
            return;
        }

        try {
            $targetVersion = $this->option('plugin-version') ?: $updateInfo['latest_version'];

            $this->components->task("Upgrading {$pluginData['name']} to v{$targetVersion}", function () use ($pluginManager, $pluginId, $targetVersion) {
                $pluginManager->upgrade($pluginId, $targetVersion);
            });

            $this->components->info("✅ Plugin upgraded successfully!");
            $this->line("   {$pluginData['name']} is now at version {$targetVersion}");

            // Suggest clearing caches
            $this->newLine();
            $this->components->info("💡 Recommended: Clear caches after upgrade");
            $this->line("   php artisan optimize:clear");

            $this->stats['succeeded']++;

        } catch (\Exception $e) {
            $this->components->error("Upgrade failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->error("Stack trace: " . $e->getTraceAsString());
            }

            $this->stats['failed']++;
        }
    }

    private function upgradeAllPlugins($pluginManager): void
    {
        $this->components->info("🔍 Checking all plugins for updates...");
        $this->newLine();

        $upgradablePlugins = [];

        foreach ($pluginManager->getAllPlugins() as $pluginId => $pluginData) {
            // Only check installed plugins from remote sources
            if ($pluginData['type'] === 'local' && $pluginData['source'] !== 'local') {
                $updateInfo = $pluginManager->getUpdateInfo($pluginId);
                if ($updateInfo) {
                    $upgradablePlugins[$pluginId] = [
                        'plugin' => $pluginData,
                        'update_info' => $updateInfo,
                    ];
                }
            }
        }

        if (empty($upgradablePlugins)) {
            $this->components->info("✅ All plugins are up to date!");
            $this->newLine();

            // Show summary of checked plugins
            $totalInstalled = $pluginManager->getAllPlugins()
                ->filter(fn($p) => $p['type'] === 'local')
                ->count();

            $this->line("   Checked {$totalInstalled} installed plugin(s)");
            $this->line("   No updates available");

            $this->stats['succeeded']++;
            return;
        }

        $count = count($upgradablePlugins);
        $this->components->warn("📦 Found {$count} plugin(s) with updates available:");
        $this->newLine();

        // Display summary table
        $tableData = [];
        foreach ($upgradablePlugins as $pluginId => $data) {
            $tableData[] = [
                $data['plugin']['name'],
                $pluginId,
                $data['update_info']['current_version'],
                $data['update_info']['latest_version'],
            ];
        }

        $this->table(
            ['Plugin Name', 'ID', 'Current', 'Available'],
            $tableData
        );

        $this->newLine();

        if ($this->dryRun) {
            $this->components->info("📋 DRY RUN: No upgrades will be performed");
            return;
        }

        // Ask for confirmation to upgrade all
        if (!$this->option('force')) {
            if (!$this->confirmDestructiveAction("Upgrade all {$count} plugin(s)?")) {
                $this->components->info('Upgrade cancelled.');
                $this->newLine();
                $this->line("💡 To upgrade individual plugins:");
                $this->line("   php artisan hyro:plugin:upgrade <plugin-id>");
                return;
            }
        }

        $this->newLine();
        $this->components->info("Starting upgrades...");
        $this->newLine();

        $successful = 0;
        $failed = 0;

        foreach ($upgradablePlugins as $pluginId => $data) {
            try {
                $this->line("⚙️  Upgrading {$data['plugin']['name']}...");
                $this->upgradeSinglePlugin($pluginId, $pluginManager);
                $successful++;
            } catch (\Exception $e) {
                $this->components->error("  Failed: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->line('┌────────────────────────────────────────────────────────┐');
        $this->line('│                   UPGRADE SUMMARY                      │');
        $this->line('├────────────────────────────────────────────────────────┤');
        $this->line(sprintf('│ Total:      %-42s │', "{$count} plugin(s)"));
        $this->line(sprintf('│ <fg=green>Successful: %-42s</> │', "{$successful} plugin(s)"));
        $this->line(sprintf('│ <fg=red>Failed:     %-42s</> │', "{$failed} plugin(s)"));
        $this->line('└────────────────────────────────────────────────────────┘');

        if ($successful > 0) {
            $this->newLine();
            $this->components->info("💡 Recommended: Clear caches after upgrades");
            $this->line("   php artisan optimize:clear");
        }
    }

    private function displayUpdateInfo(array $pluginData, array $updateInfo): void
    {
        $this->newLine();
        $this->line('┌────────────────────────────────────────────────────────┐');
        $this->line('│              UPDATE AVAILABLE                          │');
        $this->line('├────────────────────────────────────────────────────────┤');

        $name = str_pad($pluginData['name'], 50);
        $current = str_pad($updateInfo['current_version'], 50);
        $latest = str_pad($updateInfo['latest_version'], 50);

        $this->line("│ Plugin:   {$name} │");
        $this->line("│ Current:  <fg=yellow>{$current}</> │");
        $this->line("│ Latest:   <fg=green>{$latest}</> │");
        $this->line('└────────────────────────────────────────────────────────┘');

        if (!empty($updateInfo['changelog'])) {
            $this->newLine();
            $this->line("<fg=cyan>Changelog:</>");
            $this->line($updateInfo['changelog']);
        }

        $this->newLine();
    }
}

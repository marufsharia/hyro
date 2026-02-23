<?php

namespace Marufsharia\Hyro\Plugin\Console\Commands\Plugin;

use Marufsharia\Hyro\Core\Console\Commands\BaseCommand;
use Illuminate\Support\Collection;

class PluginMarketplaceCommand extends BaseCommand
{
    protected $signature = 'hyro:plugin:marketplace
        {search? : Search query}
        {--category= : Filter by category}
        {--source= : Filter by source (github, gitlab, marketplace, packagist)}
        {--installed : Show only installed plugins}
        {--available : Show only available (not installed) plugins}
        {--updatable : Show only plugins with updates}
        {--format= : Output format (table|compact)}';

    protected $description = 'Browse and search the plugin marketplace';

    protected function executeCommand(): void
    {
        $searchQuery = $this->argument('search');
        $pluginManager = app('hyro.plugins');

        $this->displayMarketplaceHeader();

        // Refresh marketplace data
        $this->components->task('Loading marketplace data', function () use ($pluginManager) {
            $pluginManager->discover(true);
        });

        $plugins = $pluginManager->getAllPlugins();

        // Apply filters
        $plugins = $this->applyFilters($plugins, $pluginManager, $searchQuery);

        if ($plugins->isEmpty()) {
            $this->displayNoResults($searchQuery);
            return;
        }

        $format = $this->option('format') ?? 'table';

        if ($format === 'compact') {
            $this->displayCompact($plugins, $pluginManager);
        } else {
            $this->displayMarketplace($plugins, $pluginManager);
        }

        $this->stats['succeeded']++;
    }

    private function applyFilters(Collection $plugins, $pluginManager, ?string $searchQuery): Collection
    {
        // Filter by installation status
        if ($this->option('installed')) {
            $plugins = $plugins->filter(fn($plugin) => $plugin['type'] === 'local');
        }

        if ($this->option('available')) {
            $plugins = $plugins->filter(fn($plugin) => $plugin['type'] === 'remote');
        }

        if ($this->option('updatable')) {
            $plugins = $plugins->filter(function ($plugin, $id) use ($pluginManager) {
                return $plugin['type'] === 'local' && $pluginManager->getUpdateInfo($id);
            });
        }

        // Search filter
        if ($searchQuery) {
            $plugins = $pluginManager->search($searchQuery, $this->option('source'));
        }

        // Source filter
        if ($sourceFilter = $this->option('source')) {
            $plugins = $plugins->filter(fn($plugin) => $plugin['source'] === $sourceFilter);
        }

        return $plugins;
    }

    private function displayMarketplaceHeader(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════════╗');
        $this->line('║                    HYRO PLUGIN MARKETPLACE                       ║');
        $this->line('╚══════════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displayMarketplace(Collection $plugins, $pluginManager): void
    {
        $tableData = [];
        $installedCount = 0;
        $updatableCount = 0;

        foreach ($plugins as $id => $plugin) {
            $isInstalled = $plugin['type'] === 'local';
            $status = $isInstalled ? '<fg=green>✅ Installed</>' : '<fg=blue>📥 Available</>';

            if ($isInstalled) {
                $installedCount++;
                $updateInfo = $pluginManager->getUpdateInfo($id);

                if ($updateInfo) {
                    $updatableCount++;
                    $status = "<fg=yellow>🔄 Update available ({$updateInfo['current_version']} → {$updateInfo['latest_version']})</>";
                }
            }

            // Format source with icon
            $source = match ($plugin['source']) {
                'local' => '💻 Local',
                'marketplace' => '🏪 Marketplace',
                'github' => '🐙 GitHub',
                'gitlab' => '🦊 GitLab',
                'packagist' => '📦 Packagist',
                default => ucfirst($plugin['source']),
            };

            // Truncate description if too long
            $description = $plugin['description'] ?? 'No description';
            if (strlen($description) > 50) {
                $description = substr($description, 0, 47) . '...';
            }

            $tableData[] = [
                $id,
                $plugin['name'],
                $description,
                $plugin['version'],
                $plugin['author'],
                $source,
                $status,
            ];
        }

        $this->table(
            [
                '<fg=cyan>ID</>',
                '<fg=cyan>Name</>',
                '<fg=cyan>Description</>',
                '<fg=cyan>Version</>',
                '<fg=cyan>Author</>',
                '<fg=cyan>Source</>',
                '<fg=cyan>Status</>',
            ],
            $tableData
        );

        $this->displayMarketplaceSummary($plugins, $installedCount, $updatableCount);
        $this->displayQuickActions($plugins, $pluginManager);
    }

    private function displayMarketplaceSummary(Collection $plugins, int $installedCount, int $updatableCount): void
    {
        $this->newLine();
        $this->line('┌────────────────────────────────────────────────────────────┐');
        $this->line('│                       SUMMARY                              │');
        $this->line('├────────────────────────────────────────────────────────────┤');

        $total = $plugins->count();
        $available = $total - $installedCount;

        $this->line(sprintf('│ Total Plugins:    <fg=white>%-38s</>', $total . ' plugin(s)'));
        $this->line(sprintf('│ <fg=green>✅ Installed:</>      <fg=green>%-38s</>', $installedCount . ' plugin(s)'));
        $this->line(sprintf('│ <fg=blue>📥 Available:</>      <fg=blue>%-38s</>', $available . ' plugin(s)'));
        $this->line(sprintf('│ <fg=yellow>🔄 Updates:</>        <fg=yellow>%-38s</>', $updatableCount . ' plugin(s)'));
        $this->line('└────────────────────────────────────────────────────────────┘');
    }

    private function displayQuickActions(Collection $plugins, $pluginManager): void
    {
        $this->newLine();
        $this->components->info('Quick Actions:');

        // Find an available plugin
        $availablePlugin = $plugins->first(fn($p) => $p['type'] === 'remote');
        if ($availablePlugin) {
            $this->line("  Install:   <fg=cyan>php artisan hyro:plugin:install-remote {$availablePlugin['source']} {$availablePlugin['id']}</>");
        }

        // Find an updatable plugin
        $updatablePlugin = null;
        foreach ($plugins as $id => $plugin) {
            if ($plugin['type'] === 'local' && $pluginManager->getUpdateInfo($id)) {
                $updatablePlugin = $plugin;
                $updatableId = $id;
                break;
            }
        }

        if ($updatablePlugin) {
            $this->line("  Upgrade:   <fg=yellow>php artisan hyro:plugin:upgrade {$updatableId}</>");
        }

        $this->newLine();
        $this->line("  Filters:");
        $this->line("    • Installed only:  <fg=gray>php artisan hyro:plugin:marketplace --installed</>");
        $this->line("    • Available only:  <fg=gray>php artisan hyro:plugin:marketplace --available</>");
        $this->line("    • Updates only:    <fg=gray>php artisan hyro:plugin:marketplace --updatable</>");
        $this->line("    • Search:          <fg=gray>php artisan hyro:plugin:marketplace 'search term'</>");
        $this->line("    • By source:       <fg=gray>php artisan hyro:plugin:marketplace --source=github</>");
    }

    private function displayCompact(Collection $plugins, $pluginManager): void
    {
        $this->components->info('Marketplace Plugins:');
        $this->newLine();

        foreach ($plugins as $id => $plugin) {
            $isInstalled = $plugin['type'] === 'local';
            $icon = $isInstalled ? '<fg=green>✅</>' : '<fg=blue>📥</>';

            if ($isInstalled) {
                $updateInfo = $pluginManager->getUpdateInfo($id);
                if ($updateInfo) {
                    $icon = '<fg=yellow>🔄</>';
                }
            }

            $line = sprintf(
                '  %s <fg=white>%s</> <fg=gray>(%s)</> - v%s',
                $icon,
                $plugin['name'],
                $id,
                $plugin['version']
            );

            $this->line($line);
        }

        $this->newLine();
        $this->line('<fg=gray>Use --format=table for detailed view</>');
    }

    private function displayNoResults(?string $searchQuery): void
    {
        $this->newLine();
        $this->components->warn('No plugins found matching your criteria.');
        $this->newLine();

        if ($searchQuery) {
            $this->line("  Search term: <fg=yellow>{$searchQuery}</>");
            $this->newLine();
            $this->components->info('Try:');
            $this->line("  • Broader search terms");
            $this->line("  • Remove filters");
            $this->line("  • Check spelling");
        } else {
            $this->components->info('Possible reasons:');
            $this->line("  • No plugins available from configured sources");
            $this->line("  • Network connectivity issues");
            $this->line("  • API keys not configured");
        }

        $this->newLine();
        $this->line("  View all: <fg=cyan>php artisan hyro:plugin:marketplace</>");

        $this->stats['succeeded']++;
    }
}

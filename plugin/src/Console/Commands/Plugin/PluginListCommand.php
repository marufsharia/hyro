<?php

namespace Marufsharia\Hyro\Plugin\Console\Commands\Plugin;

use Marufsharia\Hyro\Core\Console\Commands\BaseCommand;
use Illuminate\Support\Facades\File;

class PluginListCommand extends BaseCommand
{
    protected $signature = 'hyro:plugin:list
        {--filter= : Filter by status (all|active|inactive|installed|not-installed)}
        {--format= : Output format (table|json|compact)}
        {--sort= : Sort by field (name|status|version|author)}
        {--debug : Show debug information}';

    protected $description = 'List all available Hyro plugins with detailed information';

    protected function executeCommand(): void
    {
        $pluginManager = app('hyro.plugins');

        if ($this->option('debug')) {
            $this->debugPluginDiscovery($pluginManager);
            return;
        }

        // Force refresh to get latest state
        $pluginManager->discover(true);

        $plugins = $pluginManager->getAllPlugins();

        if ($plugins->isEmpty()) {
            $this->displayNoPluginsFound();
            return;
        }

        // Apply filters and get formatted data
        $tableData = $this->prepareTableData($plugins, $pluginManager);

        if (empty($tableData)) {
            $this->components->warn('No plugins match the specified filters.');
            $this->stats['succeeded']++;
            return;
        }

        // Sort data if requested
        if ($sortBy = $this->option('sort')) {
            $tableData = $this->sortTableData($tableData, $sortBy);
        }

        // Display based on format
        $format = $this->option('format') ?? 'table';

        switch ($format) {
            case 'json':
                $this->displayAsJson($tableData);
                break;
            case 'compact':
                $this->displayCompact($tableData);
                break;
            default:
                $this->displayAsTable($tableData, $pluginManager);
                break;
        }

        $this->stats['succeeded']++;
    }

    /**
     * Prepare table data with professional formatting
     */
    protected function prepareTableData($plugins, $pluginManager): array
    {
        $tableData = [];
        $filter = $this->option('filter') ?? 'all';

        foreach ($plugins as $id => $data) {
            $isLoaded = $pluginManager->isLoaded($id);
            $isInstalled = $data['type'] === 'local';
            $isActive = $pluginManager->isPluginActive($id);

            // Apply filters
            if (!$this->matchesFilter($filter, $isLoaded, $isInstalled, $isActive)) {
                continue;
            }

            $status = $this->getPluginStatus($isInstalled, $isLoaded, $isActive);
            $statusIcon = $this->getStatusIcon($isInstalled, $isLoaded, $isActive);
            $statusColor = $this->getStatusColor($isInstalled, $isLoaded, $isActive);

            $tableData[] = [
                'id' => $id,
                'name' => $data['meta']['name'] ?? $id,
                'version' => $data['meta']['version'] ?? 'Unknown',
                'author' => $data['meta']['author'] ?? 'Unknown',
                'status' => $status,
                'status_icon' => $statusIcon,
                'status_color' => $statusColor,
                'dependencies' => !empty($data['meta']['dependencies'])
                    ? implode(', ', $data['meta']['dependencies'])
                    : '-',
                'source' => $this->formatSource($data['source'] ?? 'local'),
                'is_loaded' => $isLoaded,
                'is_installed' => $isInstalled,
                'is_active' => $isActive,
            ];
        }

        return $tableData;
    }

    /**
     * Check if plugin matches the filter
     */
    protected function matchesFilter(string $filter, bool $isLoaded, bool $isInstalled, bool $isActive): bool
    {
        return match ($filter) {
            'active' => $isActive && $isLoaded,
            'inactive' => $isInstalled && !$isActive,
            'installed' => $isInstalled,
            'not-installed' => !$isInstalled,
            'all' => true,
            default => true,
        };
    }

    /**
     * Get plugin status description
     */
    protected function getPluginStatus(bool $isInstalled, bool $isLoaded, bool $isActive): string
    {
        if (!$isInstalled) {
            return 'Available';
        }

        if ($isLoaded && $isActive) {
            return 'Active';
        }

        if ($isInstalled && !$isActive) {
            return 'Inactive';
        }

        return 'Installed';
    }

    /**
     * Get status icon
     */
    protected function getStatusIcon(bool $isInstalled, bool $isLoaded, bool $isActive): string
    {
        if (!$isInstalled) {
            return '📥'; // Available for install
        }

        if ($isLoaded && $isActive) {
            return '✅'; // Active
        }

        if ($isInstalled && !$isActive) {
            return '⏸️'; // Inactive/Paused
        }

        return '📦'; // Installed
    }

    /**
     * Get status color for terminal
     */
    protected function getStatusColor(bool $isInstalled, bool $isLoaded, bool $isActive): string
    {
        if (!$isInstalled) {
            return 'blue';
        }

        if ($isLoaded && $isActive) {
            return 'green';
        }

        if ($isInstalled && !$isActive) {
            return 'yellow';
        }

        return 'gray';
    }

    /**
     * Format source display
     */
    protected function formatSource(string $source): string
    {
        return match ($source) {
            'local' => '💻 Local',
            'marketplace' => '🏪 Marketplace',
            'github' => '🐙 GitHub',
            'gitlab' => '🦊 GitLab',
            'packagist' => '📦 Packagist',
            default => ucfirst($source),
        };
    }

    /**
     * Display as professional table
     */
    protected function displayAsTable(array $tableData, $pluginManager): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════════════════════╗');
        $this->line('║                          HYRO PLUGIN MANAGER                                 ║');
        $this->line('╚══════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Prepare table rows with colors
        $rows = [];
        foreach ($tableData as $plugin) {
            $statusDisplay = sprintf(
                '<%s>%s %s</>',
                'fg=' . $plugin['status_color'],
                $plugin['status_icon'],
                $plugin['status']
            );

            $rows[] = [
                $plugin['id'],
                $plugin['name'],
                $plugin['version'],
                $plugin['author'],
                $statusDisplay,
                $plugin['source'],
                $plugin['dependencies'],
            ];
        }

        $this->table(
            [
                '<fg=cyan>Plugin ID</>',
                '<fg=cyan>Name</>',
                '<fg=cyan>Version</>',
                '<fg=cyan>Author</>',
                '<fg=cyan>Status</>',
                '<fg=cyan>Source</>',
                '<fg=cyan>Dependencies</>',
            ],
            $rows
        );

        $this->displaySummary($tableData, $pluginManager);
        $this->displayLegend();
        $this->displayQuickActions($tableData);
    }

    /**
     * Display summary statistics
     */
    protected function displaySummary(array $tableData, $pluginManager): void
    {
        $this->newLine();
        $this->line('┌────────────────────────────────────────────────────────────────┐');
        $this->line('│                         SUMMARY                                │');
        $this->line('├────────────────────────────────────────────────────────────────┤');

        $total = count($tableData);
        $active = count(array_filter($tableData, fn($p) => $p['is_active']));
        $inactive = count(array_filter($tableData, fn($p) => $p['is_installed'] && !$p['is_active']));
        $available = count(array_filter($tableData, fn($p) => !$p['is_installed']));

        $this->line(sprintf('│ Total Plugins:      <fg=white>%-38s</>', $total . ' plugins'));
        $this->line(sprintf('│ <fg=green>✅ Active:</>          <fg=green>%-38s</>', $active . ' plugins'));
        $this->line(sprintf('│ <fg=yellow>⏸️  Inactive:</>        <fg=yellow>%-38s</>', $inactive . ' plugins'));
        $this->line(sprintf('│ <fg=blue>📥 Available:</>       <fg=blue>%-38s</>', $available . ' plugins'));
        $this->line('└────────────────────────────────────────────────────────────────┘');
    }

    /**
     * Display status legend
     */
    protected function displayLegend(): void
    {
        $this->newLine();
        $this->components->info('Status Legend:');
        $this->line('  <fg=green>✅ Active</>      - Plugin is installed, activated, and running');
        $this->line('  <fg=yellow>⏸️  Inactive</>    - Plugin is installed but not activated');
        $this->line('  <fg=blue>📥 Available</>   - Plugin is available but not installed');
        $this->line('  💻 Local       - Installed from local directory');
        $this->line('  🏪 Marketplace - Available from Hyro Marketplace');
    }

    /**
     * Display quick action hints
     */
    protected function displayQuickActions(array $tableData): void
    {
        $this->newLine();
        $this->components->info('Quick Actions:');

        // Find an inactive plugin to suggest activation
        $inactivePlugin = collect($tableData)->first(fn($p) => $p['is_installed'] && !$p['is_active']);
        if ($inactivePlugin) {
            $this->line("  Activate:   <fg=green>php artisan hyro:plugin:activate {$inactivePlugin['id']}</>");
        }

        // Find an active plugin to suggest deactivation
        $activePlugin = collect($tableData)->first(fn($p) => $p['is_active']);
        if ($activePlugin) {
            $this->line("  Deactivate: <fg=yellow>php artisan hyro:plugin:deactivate {$activePlugin['id']}</>");
        }

        // Find an available plugin to suggest installation
        $availablePlugin = collect($tableData)->first(fn($p) => !$p['is_installed']);
        if ($availablePlugin) {
            $this->line("  Install:    <fg=blue>php artisan hyro:plugin:install {$availablePlugin['id']}</>");
        }

        $this->newLine();
        $this->line("  Filters:    <fg=cyan>php artisan hyro:plugin:list --filter=active</>");
        $this->line("  Formats:    <fg=cyan>php artisan hyro:plugin:list --format=json</>");
        $this->line("  Sort:       <fg=cyan>php artisan hyro:plugin:list --sort=name</>");
    }

    /**
     * Display compact format
     */
    protected function displayCompact(array $tableData): void
    {
        $this->components->info('Installed Plugins:');
        $this->newLine();

        foreach ($tableData as $plugin) {
            $statusIcon = $plugin['status_icon'];
            $statusColor = $plugin['status_color'];

            $line = sprintf(
                '  <%s>%s</> <fg=white>%s</> <fg=gray>(%s)</> - %s',
                'fg=' . $statusColor,
                $statusIcon,
                $plugin['name'],
                $plugin['id'],
                $plugin['version']
            );

            $this->line($line);
        }

        $this->newLine();
        $this->line('<fg=gray>Use --format=table for detailed view</>');
    }

    /**
     * Display as JSON
     */
    protected function displayAsJson(array $tableData): void
    {
        $output = array_map(function ($plugin) {
            return [
                'id' => $plugin['id'],
                'name' => $plugin['name'],
                'version' => $plugin['version'],
                'author' => $plugin['author'],
                'status' => $plugin['status'],
                'source' => $plugin['source'],
                'dependencies' => $plugin['dependencies'],
                'is_active' => $plugin['is_active'],
                'is_installed' => $plugin['is_installed'],
            ];
        }, $tableData);

        $this->line(json_encode($output, JSON_PRETTY_PRINT));
    }

    /**
     * Sort table data
     */
    protected function sortTableData(array $tableData, string $sortBy): array
    {
        usort($tableData, function ($a, $b) use ($sortBy) {
            return match ($sortBy) {
                'name' => strcasecmp($a['name'], $b['name']),
                'status' => strcasecmp($a['status'], $b['status']),
                'version' => version_compare($a['version'], $b['version']),
                'author' => strcasecmp($a['author'], $b['author']),
                default => 0,
            };
        });

        return $tableData;
    }

    /**
     * Display when no plugins found
     */
    protected function displayNoPluginsFound(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                    NO PLUGINS FOUND                          ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));

        $this->components->warn('No plugins are currently available.');
        $this->newLine();

        $this->line('📁 Plugins Directory:');
        $this->line("   Path: {$pluginsPath}");
        $this->line("   Exists: " . (File::exists($pluginsPath) ? '<fg=green>Yes</>' : '<fg=red>No</>'));
        $this->newLine();

        $this->components->info('Get Started:');
        $this->line('  1. Create a new plugin:');
        $this->line('     <fg=cyan>php artisan hyro:plugin:make MyPlugin</>');
        $this->newLine();
        $this->line('  2. Or install from marketplace:');
        $this->line('     <fg=cyan>php artisan hyro:plugin:marketplace</>');
        $this->newLine();

        $this->stats['succeeded']++;
    }

    /**
     * Debug plugin discovery
     */
    protected function debugPluginDiscovery($pluginManager): void
    {
        $this->components->info("🔍 PLUGIN SYSTEM DIAGNOSTICS");
        $this->newLine();

        // 1. Directory Check
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('<fg=cyan>1. DIRECTORY STRUCTURE</>');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
        $this->line("Plugins Path:     {$pluginsPath}");
        $this->line("Directory Exists: " . (File::exists($pluginsPath) ? '<fg=green>✓ Yes</>' : '<fg=red>✗ No</>'));
        $this->line("Is Directory:     " . (File::isDirectory($pluginsPath) ? '<fg=green>✓ Yes</>' : '<fg=red>✗ No</>'));
        $this->newLine();

        if (File::isDirectory($pluginsPath)) {
            $directories = File::directories($pluginsPath);
            $this->line("Subdirectories:   <fg=yellow>" . count($directories) . "</>");

            foreach ($directories as $dir) {
                // Check for Plugin.php in both src and root directory
                $pluginFile = $dir . '/src/Plugin.php';
                if (!File::exists($pluginFile)) {
                    $pluginFile = $dir . '/Plugin.php';
                }
                
                $pluginName = basename($dir);
                $hasPlugin = File::exists($pluginFile);

                $icon = $hasPlugin ? '<fg=green>✓</>' : '<fg=red>✗</>';
                $this->line("  {$icon} {$pluginName}");

                if ($hasPlugin && $this->output->isVerbose()) {
                    $content = file_get_contents($pluginFile);
                    if (preg_match('/namespace\s+([^;]+)/', $content, $matches)) {
                        $this->line("      Namespace: <fg=gray>{$matches[1]}</>");
                    }
                }
            }
        }

        // 2. Plugin Manager State
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('<fg=cyan>2. PLUGIN MANAGER STATE</>');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $pluginManager->discover(true);
        $plugins = $pluginManager->getAllPlugins();
        $loadedPlugins = $pluginManager->getLoadedPlugins();

        $this->line("Discovered:       <fg=yellow>{$plugins->count()}</> plugins");
        $this->line("Loaded in Memory: <fg=green>{$loadedPlugins->count()}</> plugins");
        $this->newLine();

        foreach ($plugins as $id => $data) {
            $isLoaded = $pluginManager->isLoaded($id);
            $icon = $isLoaded ? '<fg=green>✓ Loaded</>' : '<fg=red>✗ Not Loaded</>';

            $this->line("  {$icon} <fg=white>{$data['meta']['name']}</> <fg=gray>({$id})</>");
            if ($this->output->isVerbose()) {
                $this->line("      Type:    {$data['type']}");
                $this->line("      Version: {$data['meta']['version']}");
                $this->line("      Path:    {$data['path']}");
            }
        }

        // 3. State File
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('<fg=cyan>3. PERSISTENCE STATE</>');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $stateFile = storage_path('hyro/plugins-state.json');
        $this->line("State File:   {$stateFile}");
        $this->line("File Exists:  " . (File::exists($stateFile) ? '<fg=green>✓ Yes</>' : '<fg=red>✗ No</>'));

        if (File::exists($stateFile)) {
            $states = json_decode(File::get($stateFile), true);
            $activeCount = count(array_filter($states, fn($s) => $s['active'] ?? false));

            $this->line("Total States: <fg=yellow>" . count($states) . "</>");
            $this->line("Active:       <fg=green>{$activeCount}</>");
            $this->newLine();

            foreach ($states as $id => $state) {
                $active = $state['active'] ?? false;
                $icon = $active ? '<fg=green>✓ Active</>' : '<fg=yellow>○ Inactive</>';
                $timestamp = $active ? ($state['activated_at'] ?? 'N/A') : ($state['deactivated_at'] ?? 'N/A');

                $this->line("  {$icon} {$id}");
                if ($this->output->isVerbose()) {
                    $this->line("      Last Changed: <fg=gray>{$timestamp}</>");
                }
            }
        }

        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}

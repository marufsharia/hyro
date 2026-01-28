<?php
// ============================================
// Plugin Uninstall Command
// ============================================

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Console\Commands\BaseCommand;

class PluginUninstallCommand extends BaseCommand
{
    protected $signature = 'hyro:plugin:uninstall {plugin : Plugin ID, name, or path} {--force : Force uninstallation} {--delete : Delete plugin files after uninstall}';
    protected $description = 'Uninstall a Hyro plugin (rollback migrations and run uninstall hook)';

    protected function executeCommand(): void
    {
        $inputName = $this->argument('plugin');

        // 1. Refresh Plugin Manager to ensure we have latest state
        $pluginManager = app('hyro.plugins');
        $pluginManager->refresh();

        // 2. Try multiple strategies to find the plugin
        $pluginId = $this->resolvePluginId($inputName, $pluginManager);

        if (!$pluginId) {
            $this->components->error("❌ Plugin '{$inputName}' not found.");
            $this->displayAvailablePlugins($pluginManager);
            $this->stats['failed']++;
            return;
        }

        $pluginData = $pluginManager->getAllPlugins()->get($pluginId);
        $name = $pluginData['meta']['name'] ?? $pluginId;
        $pluginPath = $pluginData['path'] ?? null;

        $this->components->warn("🗑️  Uninstalling plugin: {$name} ({$pluginId})");
        $this->line("   This will rollback migrations and remove data associated with this plugin.");

        if ($this->option('delete')) {
            $this->components->caution("   ⚠️  DELETE FLAG ENABLED: Plugin files will be permanently deleted!");
        }

        $this->newLine();

        // 3. Confirm Action
        if (!$this->option('force')) {
            $message = 'Are you sure you want to uninstall this plugin?';
            if ($this->option('delete')) {
                $message .= ' THIS WILL DELETE ALL PLUGIN FILES PERMANENTLY!';
            }

            if (!$this->confirmDestructiveAction($message)) {
                $this->components->info('Uninstallation cancelled.');
                return;
            }
        }

        // 4. Perform Uninstall
        try {
            // Ensure plugin is loaded so we can access its methods (like uninstall hook)
            if (!$pluginManager->isLoaded($pluginId)) {
                try {
                    $pluginManager->loadPlugin($pluginId);
                } catch (\Exception $e) {
                    // If we can't load it, we might not be able to run the uninstall hook,
                    // but we should proceed with what we can if it's just disabled.
                    $this->components->warn("Warning: Could not load plugin instance. Uninstall hook might be skipped.");
                }
            }

            $this->info("Running uninstall operations for plugin '{$name}'...");
            $this->executeInTransaction(function () use ($pluginManager, $pluginId) {
                $pluginManager->uninstall($pluginId);
            });
            $this->info("✅ Plugin uninstall operations completed.");

            // 5. Optionally delete plugin files
            if ($this->option('delete') && $pluginPath && File::exists($pluginPath)) {
                $this->components->caution("Deleting plugin files from: {$pluginPath}");

                // Double confirmation for deletion
                if (!$this->option('force')) {
                    if (!$this->confirmDestructiveAction("⚠️  FINAL WARNING: This will PERMANENTLY delete all plugin files at: {$pluginPath}")) {
                        $this->components->info('File deletion cancelled. Plugin uninstalled but files remain.');
                    } else {
                        $this->deletePluginFiles($pluginPath);
                    }
                } else {
                    $this->deletePluginFiles($pluginPath);
                }
            }

            $this->newLine();
            $this->components->info("✅ Plugin '{$name}' uninstalled successfully!");

            if ($this->option('delete')) {
                $this->components->info("🗑️  Plugin files have been permanently deleted.");
                $this->line("   Note: Clear route cache if you had issues accessing the plugin.");
                $this->call('route:clear');
            } else {
                $this->components->info("📝 Plugin files remain in the filesystem.");
                $this->line("   The plugin will still be discovered but won't be active.");
                $this->line("   To completely remove, delete manually or use --delete flag.");
            }

            $this->stats['succeeded']++;

        } catch (\Exception $e) {
            $this->components->error("❌ Failed to uninstall plugin: {$e->getMessage()}");
            $this->stats['failed']++;
        }
    }

    /**
     * Delete plugin files safely
     */
    private function deletePluginFiles(string $pluginPath): void
    {
        try {
            // Check if it's within the plugins directory for safety
            $pluginsBasePath = config('hyro.plugins.path', base_path('hyro-plugins'));

            if (strpos($pluginPath, $pluginsBasePath) !== 0) {
                throw new \Exception('Plugin path is not within the hyro-plugins directory. Aborting for safety.');
            }

            // Count files before deletion
            $fileCount = count(File::allFiles($pluginPath));
            $dirCount = count(File::directories($pluginPath));

            // Delete the directory
            File::deleteDirectory($pluginPath);

            if (!File::exists($pluginPath)) {
                $this->components->info("✅ Deleted {$fileCount} files and {$dirCount} directories.");
            } else {
                throw new \Exception('Failed to delete some files.');
            }

        } catch (\Exception $e) {
            throw new \Exception("Failed to delete plugin files: " . $e->getMessage());
        }
    }

    /**
     * Resolve plugin ID from various input formats
     */
    private function resolvePluginId(string $input, $pluginManager): ?string
    {
        $plugins = $pluginManager->getAllPlugins();

        // Strategy 1: Exact match on plugin ID
        if ($plugins->has($input)) {
            return $input;
        }

        // Strategy 2: Kebab-case of input
        $kebabId = Str::kebab($input);
        if ($plugins->has($kebabId)) {
            return $kebabId;
        }

        // Strategy 3: Case-insensitive match on plugin ID
        $inputLower = strtolower($input);
        foreach ($plugins as $id => $data) {
            if (strtolower($id) === $inputLower) {
                return $id;
            }
        }

        // Strategy 4: Match on plugin name (from meta)
        foreach ($plugins as $id => $data) {
            $pluginName = $data['meta']['name'] ?? null;
            if ($pluginName && strtolower($pluginName) === $inputLower) {
                return $id;
            }

            // Also try kebab-case of plugin name
            if ($pluginName) {
                $kebabName = Str::kebab($pluginName);
                if (strtolower($kebabName) === $inputLower) {
                    return $id;
                }
            }
        }

        // Strategy 5: Partial match (if input is part of plugin name)
        foreach ($plugins as $id => $data) {
            $pluginName = $data['meta']['name'] ?? $id;

            // Remove all non-alphanumeric characters and compare
            $cleanInput = preg_replace('/[^a-zA-Z0-9]/', '', $input);
            $cleanPluginName = preg_replace('/[^a-zA-Z0-9]/', '', $pluginName);
            $cleanId = preg_replace('/[^a-zA-Z0-9]/', '', $id);

            if (strcasecmp($cleanInput, $cleanPluginName) === 0 ||
                strcasecmp($cleanInput, $cleanId) === 0) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Display available plugins to help users
     */
    private function displayAvailablePlugins($pluginManager): void
    {
        $plugins = $pluginManager->getAllPlugins();

        if ($plugins->isEmpty()) {
            $this->newLine();
            $this->comment("No plugins are currently registered.");
            return;
        }

        $this->newLine();
        $this->comment("Available plugins:");
        $this->newLine();

        $rows = [];
        foreach ($plugins as $id => $data) {
            $name = $data['meta']['name'] ?? $id;
            $description = $data['meta']['description'] ?? 'No description';
            $version = $data['meta']['version'] ?? 'N/A';
            $status = $data['loaded'] ? '✅ Loaded' : '❌ Not loaded';

            $rows[] = [
                'ID' => $id,
                'Name' => $name,
                'Version' => $version,
                'Description' => $description,
                'Status' => $status
            ];
        }

        $this->table(['ID', 'Name', 'Version', 'Description', 'Status'], $rows);

        $this->newLine();
        $this->comment("Usage examples:");
        $this->line("  - By ID: php artisan hyro:plugin:uninstall a-b-c-e");
        $this->line("  - By name: php artisan hyro:plugin:uninstall ABCE");
        $this->line("  - With force: php artisan hyro:plugin:uninstall a-b-c-e --force");
        $this->line("  - With delete: php artisan hyro:plugin:uninstall a-b-c-e --delete (permanently removes files)");
    }
}

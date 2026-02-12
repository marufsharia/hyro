<?php

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Commands\Plugin\Concerns\PluginResolver;

class PluginInstallRemoteCommand extends BaseCommand
{
    use PluginResolver;

    protected $signature = 'hyro:plugin:install-remote
        {source : Source (marketplace, github, gitlab, packagist, url)}
        {identifier : Plugin identifier, repo URL, or package name}
        {--plugin-version= : Specific plugin version to install}
        {--no-activate : Do not activate plugin after installation}
        {--force : Skip confirmation}';

    protected $description = 'Install a plugin from a remote source';

    protected function executeCommand(): void
    {
        $source = $this->argument('source');
        $identifier = $this->argument('identifier');
        $version = $this->option('plugin-version');

        $pluginManager = app('hyro.plugins');

        // Refresh plugin list to get latest remote data
        $pluginManager->discover(true);

        $this->components->info("🔍 Searching for plugin from {$source}...");

        try {
            // Special handling for direct URL installations
            if ($source === 'url') {
                $this->installFromUrl($identifier, $version, $pluginManager);
                return;
            }

            // For other sources (marketplace, github, gitlab, packagist)
            $pluginId = $this->resolvePluginId($source, $identifier, $pluginManager);

            if (!$pluginId) {
                $this->components->error("Plugin not found in source: {$source}");
                $this->displaySourceHelp($source);
                $this->stats['failed']++;
                return;
            }

            $pluginData = $pluginManager->getAllPlugins()->get($pluginId);

            if (!$pluginData) {
                $this->components->error("Plugin '{$pluginId}' not found in discovered plugins.");
                $this->stats['failed']++;
                return;
            }

            if ($pluginData['type'] !== 'remote') {
                $this->components->warn("Plugin '{$pluginId}' is already installed locally.");
                $this->line("   Use: php artisan hyro:plugin:activate {$pluginId}");
                $this->stats['failed']++;
                return;
            }

            $this->displayPluginInfo($pluginData);

            if (!$this->option('force') && !$this->confirmDestructiveAction('Install this plugin?')) {
                $this->components->info('Installation cancelled.');
                return;
            }

            $this->components->task('Downloading and installing plugin', function () use ($pluginManager, $pluginId, $pluginData, $version) {
                $pluginManager->installRemote($pluginId, $pluginData['source'], $version);
            });

            $this->components->info("✅ Plugin installed successfully!");

            // Auto-activate unless --no-activate flag is used
            $this->handlePostInstallActivation($pluginManager, $pluginId);

            $this->stats['succeeded']++;

        } catch (\Exception $e) {
            $this->components->error("Installation failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->error("Stack trace: " . $e->getTraceAsString());
            }

            $this->stats['failed']++;
        }
    }

    /**
     * Install plugin directly from a URL
     */
    protected function installFromUrl(string $url, ?string $version, $pluginManager): void
    {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->components->error("Invalid URL: {$url}");
            $this->line("   Please provide a valid Git repository URL.");
            $this->line("   Example: https://github.com/username/repo-name.git");
            $this->stats['failed']++;
            return;
        }

        // Generate plugin ID from URL
        $pluginId = $this->generatePluginIdFromUrl($url);

        // Check if already installed
        if ($pluginManager->isPluginInstalled($pluginId)) {
            $this->components->warn("Plugin '{$pluginId}' is already installed.");
            $this->line("   Location: " . config('hyro.plugins.path') . "/{$pluginId}");
            $this->newLine();

            if (!$this->option('force')) {
                if (!$this->confirmDestructiveAction('Reinstall this plugin?')) {
                    $this->components->info('Installation cancelled.');
                    return;
                }
            }
        }

        // Display URL info
        $this->displayUrlInfo($url, $pluginId, $version);

        if (!$this->option('force') && !$this->confirmDestructiveAction('Install plugin from this URL?')) {
            $this->components->info('Installation cancelled.');
            return;
        }

        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
        $installPath = $pluginsPath . '/' . $pluginId;

        try {
            // Download from Git URL
            $this->components->task('Cloning repository', function () use ($url, $installPath, $version) {
                $this->cloneFromGit($url, $installPath, $version);
            });

            // Verify it's a valid Hyro plugin
            $pluginFile = $installPath . '/src/Plugin.php';
            if (!file_exists($pluginFile)) {
                $pluginFile = $installPath . '/Plugin.php';
            }
            
            if (!file_exists($pluginFile)) {
                throw new \Exception("Downloaded repository is not a valid Hyro plugin (Plugin.php not found)");
            }

            $this->components->info("✅ Repository cloned successfully!");

            // Force rediscovery to find the newly downloaded plugin
            $this->newLine();
            $this->components->task('Discovering plugin', function () use ($pluginManager) {
                $pluginManager->discover(true);
            });

            // Now install the plugin
            $this->components->task('Installing plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->install($pluginId);
            });

            $this->components->info("✅ Plugin installed successfully!");

            // Auto-activate
            $this->handlePostInstallActivation($pluginManager, $pluginId);

            $this->stats['succeeded']++;

        } catch (\Exception $e) {
            // Clean up on failure
            if (file_exists($installPath)) {
                \Illuminate\Support\Facades\File::deleteDirectory($installPath);
            }
            throw $e;
        }
    }

    /**
     * Clone repository from Git URL
     */
    protected function cloneFromGit(string $url, string $installPath, ?string $version = null): void
    {
        // Ensure parent directory exists
        $pluginsPath = dirname($installPath);
        if (!file_exists($pluginsPath)) {
            mkdir($pluginsPath, 0755, true);
        }

        // Build git clone command
        if ($version) {
            $commands = ['git', 'clone', '--branch', $version, '--depth', '1', $url, $installPath];
        } else {
            $commands = ['git', 'clone', '--depth', '1', $url, $installPath];
        }

        $process = new \Symfony\Component\Process\Process($commands);
        $process->setTimeout(300); // 5 minutes
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("Git clone failed: " . $process->getErrorOutput());
        }
    }

    /**
     * Handle post-installation activation
     */
    protected function handlePostInstallActivation($pluginManager, string $pluginId): void
    {
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
                    $this->components->info("✅ Plugin '{$pluginId}' activated successfully!");
                    $this->line("   The plugin is now ready to use.");
                } else {
                    $this->components->warn("⚠️  Plugin installed but may not be fully activated.");
                    $this->line("   Try: php artisan hyro:plugin:activate {$pluginId}");
                }

            } catch (\Exception $e) {
                $this->components->error("⚠️  Plugin installed but activation failed: {$e->getMessage()}");
                $this->line("   Try manually: php artisan hyro:plugin:activate {$pluginId}");
            }
        } else {
            $this->newLine();
            $this->components->warn("⚠️  Plugin installed but NOT activated.");
            $this->comment("💡 Activate: php artisan hyro:plugin:activate {$pluginId}");
        }
    }

    private function resolvePluginId(string $source, string $identifier, $pluginManager): ?string
    {
        // Search in existing remote plugins
        $results = $pluginManager->search($identifier, $source);

        if ($results->isEmpty()) {
            return null;
        }

        if ($results->count() === 1) {
            return $results->keys()->first();
        }

        // Multiple matches - let users choose
        $this->components->warn("Multiple plugins found:");
        $this->newLine();

        $choices = [];
        foreach ($results as $id => $plugin) {
            $name = $plugin['name'] ?? $id;
            $desc = $plugin['description'] ?? 'No description';
            $version = $plugin['version'] ?? 'Unknown';

            $choices[$id] = "{$name} (v{$version}) - {$desc}";
        }

        return $this->choice('Select plugin:', $choices);
    }

    private function generatePluginIdFromUrl(string $url): string
    {
        // Extract repository name from URL
        // Example: https://github.com/user/repo-name.git -> repo-name
        $path = parse_url($url, PHP_URL_PATH);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        // Remove .git extension if present
        $filename = str_replace('.git', '', $filename);

        return \Illuminate\Support\Str::kebab($filename);
    }

    private function displayPluginInfo(array $pluginData): void
    {
        $this->newLine();
        $this->line('┌────────────────────────────────────────────────────────────┐');
        $this->line('│                   PLUGIN INFORMATION                       │');
        $this->line('├────────────────────────────────────────────────────────────┤');

        $name = str_pad($pluginData['name'], 50);
        $id = str_pad($pluginData['id'] ?? 'N/A', 50);
        $version = str_pad($pluginData['version'], 50);
        $author = str_pad($pluginData['author'], 50);
        $source = str_pad($pluginData['source'], 50);

        $this->line("│ Name:        {$name} │");
        $this->line("│ ID:          {$id} │");
        $this->line("│ Version:     {$version} │");
        $this->line("│ Author:      {$author} │");
        $this->line("│ Source:      {$source} │");
        $this->line('└────────────────────────────────────────────────────────────┘');

        if (!empty($pluginData['description'])) {
            $this->newLine();
            $this->line("<fg=gray>Description:</> {$pluginData['description']}");
        }

        $this->newLine();
    }

    private function displayUrlInfo(string $url, string $pluginId, ?string $version): void
    {
        $this->newLine();
        $this->line('┌────────────────────────────────────────────────────────────┐');
        $this->line('│                   URL INSTALLATION                         │');
        $this->line('├────────────────────────────────────────────────────────────┤');

        $urlPadded = str_pad(substr($url, 0, 50), 50);
        $idPadded = str_pad($pluginId, 50);
        $versionPadded = str_pad($version ?? 'latest', 50);

        $this->line("│ URL:         {$urlPadded} │");
        $this->line("│ Plugin ID:   {$idPadded} │");
        $this->line("│ Version:     {$versionPadded} │");
        $this->line('└────────────────────────────────────────────────────────────┘');

        $this->newLine();
        $this->components->warn("⚠️  Installing from a direct URL");
        $this->line("   • Make sure you trust this source");
        $this->line("   • The plugin will be cloned to: hyro-plugins/{$pluginId}");
        $this->newLine();
    }

    private function displaySourceHelp(string $source): void
    {
        $this->newLine();
        $this->components->info("💡 Troubleshooting:");

        switch ($source) {
            case 'github':
                $this->line("  • Check the repository exists and is public");
                $this->line("  • Ensure the repository has 'hyro-plugin' topic");
                $this->line("  • Example: php artisan hyro:plugin:install-remote github username/repo-name");
                break;

            case 'gitlab':
                $this->line("  • Check the project exists and is public");
                $this->line("  • Ensure it's tagged as a hyro-plugin");
                $this->line("  • Example: php artisan hyro:plugin:install-remote gitlab username/project-name");
                break;

            case 'packagist':
                $this->line("  • Check the package exists on Packagist");
                $this->line("  • Ensure package type is 'hyro-plugin'");
                $this->line("  • Example: php artisan hyro:plugin:install-remote packagist vendor/package-name");
                break;

            case 'marketplace':
                $this->line("  • Check your marketplace API key is configured");
                $this->line("  • Verify the plugin exists in Hyro Marketplace");
                $this->line("  • Example: php artisan hyro:plugin:install-remote marketplace plugin-name");
                break;
        }

        $this->newLine();
        $this->line("  Browse available plugins: <fg=cyan>php artisan hyro:plugin:marketplace</>");
    }
}

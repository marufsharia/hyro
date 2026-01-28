<?php
// ============================================
// Plugin Make Command
// ============================================
namespace MarufSharia\Hyro\Console\Commands\Plugin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Marufsharia\Hyro\Console\Commands\BaseCommand;

class PluginMakeCommand extends BaseCommand
{
    protected $signature = 'hyro:plugin:make {name : Plugin name} {--author= : Plugin author name}';
    protected $description = 'Create a new Hyro plugin scaffold';

    protected function executeCommand(): void
    {
        $name = $this->argument('name');
        $author = $this->option('author') ?? 'Your Name';
        $pluginsPath = config('hyro.plugins.path', base_path('hyro-plugins'));
        $pluginPath = $pluginsPath . '/' . $name;

        // 1. Validation
        if (File::isDirectory($pluginPath)) {
            // BaseCommand handles the exception logging
            throw new \Exception("Plugin '{$name}' already exists at {$pluginPath}!");
        }

        $this->components->info("🚀 Creating plugin '{$name}'...");
        $this->newLine();

        // 2. Create Files (Scaffolding)
        $this->components->task('Creating directory structure', function () use ($pluginPath) {
            File::makeDirectory($pluginPath, 0755, true);
            File::makeDirectory($pluginPath . '/database/migrations', 0755, true);
            File::makeDirectory($pluginPath . '/resources/views', 0755, true);
            File::makeDirectory($pluginPath . '/routes', 0755, true);
            File::makeDirectory($pluginPath . '/src', 0755, true);
        });

        $this->components->task('Creating index view', function () use ($pluginPath, $name) {
            $content = <<<HTML
<div style="padding: 20px; font-family: sans-serif;">
    <h1>Plugin: {$name}</h1>
    <p>Welcome to your new Hyro plugin!</p>
    <hr>
    <p>File location: <code>resources/views/index.blade.php</code></p>
</div>
HTML;
            File::put($pluginPath . '/resources/views/index.blade.php', $content);
        });
        $this->components->task('Creating Plugin.php', function () use ($pluginPath, $name, $author) {
            File::put($pluginPath . '/Plugin.php', $this->getPluginStub($name, $author));
        });

        $this->components->task('Creating composer.json', function () use ($pluginPath, $name) {
            File::put($pluginPath . '/composer.json', $this->getComposerStub($name));
        });

        $this->components->task('Creating README.md', function () use ($pluginPath, $name) {
            File::put($pluginPath . '/README.md', $this->getReadmeStub($name));
        });

        $this->components->task('Creating routes/web.php', function () use ($pluginPath, $name) {
            File::put($pluginPath . '/routes/web.php', $this->getRoutesStub($name));
        });

        $this->components->task('Creating .gitignore', function () use ($pluginPath) {
            File::put($pluginPath . '/.gitignore', "/vendor\n/node_modules\n.DS_Store\n");
        });

        // 3. AUTOMATION: Clear Cache & Run Composer
        $this->newLine();
        $this->components->info('⚙️  Registering plugin...');

        // Step A: Clear Hyro/Laravel Cache
        $this->components->task('Clearing plugin cache', function () {
            Cache::forget('hyro.plugins.discovered');
            // Force the manager to forget loaded plugins
            if (app()->bound('hyro.plugins')) {
                app('hyro.plugins')->refresh();
            }
        });

        // Step B: Run Composer Dump-Autoload
        $this->components->task('Running composer dump-autoload', function () {
            // Find composer executable (defaults to 'composer')
            $composer = $this->findComposer();

            $process = new Process([$composer, 'dump-autoload']);
            $process->setWorkingDirectory(base_path()); // Run in project root
            $process->setTimeout(300); // 5 minutes max
            $process->run();

            if (!$process->isSuccessful()) {
                // We don't throw exception here to avoid reverting the file creation,
                // but we warn the user.
                throw new \Exception('Composer failed: ' . $process->getErrorOutput());
            }
        });

        // 4. Final Success Message
        $this->newLine();
        $this->components->info("✅ Plugin '{$name}' created and registered!");
        $this->newLine();
        $this->line("📁 Location: {$pluginPath}");
        $this->newLine();
        $this->comment("Next steps:");
        $this->line("  1. Edit logic in {$pluginPath}/Plugin.php");
        $this->line("  2. Install: php artisan hyro:plugin:install " . $this->getPluginId($name));

        $this->stats['succeeded']++;
    }

    /**
     * Helper to find the composer executable
     */
    protected function findComposer(): string
    {
        if (File::exists(base_path('composer.phar'))) {
            return '"' . PHP_BINARY . '" composer.phar';
        }
        return 'composer';
    }

    protected function getPluginStub($name, $author)
    {
        $id = $this->getPluginId($name);

        return <<<PHP
<?php

namespace HyroPlugins\\{$name};

use Marufsharia\Hyro\Support\Plugins\HyroPlugin;

class Plugin extends HyroPlugin
{
    public function getId(): string
    {
        return '{$id}';
    }

    public function getName(): string
    {
        return '{$name}';
    }

    public function getDescription(): string
    {
        return 'Description for {$name} plugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return '{$author}';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function register(): void
    {
        // Register services here
    }

    public function boot(): void
    {
        // Boot plugin here
        \$this->info('Plugin {$name} booted!');
    }

    public function routes(): ?string
    {
        return __DIR__ . '/routes/web.php';
    }

    public function migrations(): ?string
    {
        return __DIR__ . '/database/migrations';
    }

    public function views(): ?string
    {
        return __DIR__ . '/resources/views';
    }

    public function install(): void
    {
        \$this->info('Installing {$name}...');
        // Add installation logic here
    }

    public function uninstall(): void
    {
        \$this->info('Uninstalling {$name}...');
        // Add uninstallation logic here
    }

    public function activate(): void
    {
        \$this->info('{$name} activated!');
    }

    public function deactivate(): void
    {
        \$this->info('{$name} deactivated!');
    }
}
PHP;
    }

    protected function getComposerStub($name)
    {
        $packageName = Str::kebab($name);

        return <<<JSON
{
    "name": "hyro-plugins/{$packageName}",
    "description": "{$name} plugin for Hyro",
    "type": "hyro-plugin",
    "version": "1.0.0",
    "autoload": {
        "psr-4": {
            "HyroPlugins\\\\{$name}\\\\": "src/"
        }
    },
    "require": {
        "php": "^8.2",
        "marufsharia/hyro": "^1.0"
    }
}
JSON;
    }

    protected function getReadmeStub($name)
    {
        $id = $this->getPluginId($name);

        return <<<MD
# {$name} Plugin

## Description

{$name} plugin for Hyro authorization system.

## Installation

```bash
php artisan hyro:plugin:install {$id}
php artisan hyro:plugin:activate {$id}
```

## Features

- Feature 1
- Feature 2
- Feature 3

## Configuration

Add any configuration to `config/hyro.php`:

```php
'plugins' => [
    '{$id}' => [
        'enabled' => true,
        // Add your config here
    ],
],
```

## Usage

Usage instructions here.

## Requirements

- PHP 8.2+
- Laravel 12+
- Hyro ^1.0

## Author

Your Name

## License

MIT
MD;
    }

    protected function getRoutesStub($name)
    {
        $routePrefix = Str::kebab($name);
        $id = $this->getPluginId($name);

        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('hyro/plugins/{$routePrefix}')
    ->middleware(['web', 'hyro.auth'])
    ->name('hyro.plugin.{$id}.')
    ->group(function () {
        Route::get('/', function () {
            return view('hyro-plugin-{$id}::index');
        })->name('index');

        // Add more routes here
    });
PHP;
    }

    protected function getPluginId($name)
    {
        return Str::kebab($name);
    }
}

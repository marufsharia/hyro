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
            $content = $this->getIndexViewStub($name);
            File::put($pluginPath . '/resources/views/index.blade.php', $content);
        });

        $this->components->task('Creating Plugin.php', function () use ($pluginPath, $name, $author) {
            File::put($pluginPath . '/src/Plugin.php', $this->getPluginStub($name, $author));
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
            // Find composer executable (array form compatible with Symfony Process)
            $composer = $this->findComposer();

            // Composer may return an array like [PHP_BINARY, 'composer.phar'] or ['composer']
            $command = is_array($composer) ? array_merge($composer, ['dump-autoload']) : [$composer, 'dump-autoload'];

            $process = new Process($command);
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
        $this->line("  1. Edit logic in {$pluginPath}/src/Plugin.php");
        $this->line("  2. Install: php artisan hyro:plugin:install " . $this->getPluginId($name));

        $this->stats['succeeded']++;
    }

    /**
     * Helper to find the composer executable.
     *
     * Returns an array compatible with Symfony Process (mirrors Laravel's Composer::findComposer()).
     */
    protected function findComposer(): array
    {
        if (File::exists(base_path('composer.phar'))) {
            return [PHP_BINARY, 'composer.phar'];
        }

        return ['composer'];
    }

    protected function getPluginStub($name, $author)
    {
        $id = $this->getPluginId($name);
        $routePrefix = Str::kebab($name);
        
        $stub = <<<'PHP'
<?php

namespace HyroPlugins\{NAME};

use Marufsharia\Hyro\Support\Plugins\HyroPlugin;

class Plugin extends HyroPlugin
{
    public function getId(): string
    {
        return '{ID}';
    }

    public function getName(): string
    {
        return '{NAME}';
    }

    public function getDescription(): string
    {
        return 'Description for {NAME} plugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return '{AUTHOR}';
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
        $this->info('Plugin {NAME} booted!');
    }

    public function routes(): ?string
    {
        return __DIR__ . '/../routes/web.php';
    }

    public function migrations(): ?string
    {
        return __DIR__ . '/../database/migrations';
    }

    public function views(): ?string
    {
        return __DIR__ . '/../resources/views';
    }

    public function install(): void
    {
        parent::install();
        
        $this->info('Installing {NAME}...');
        
        // Run migrations automatically
        if ($this->migrations()) {
            $this->runMigrations();
        }
        
        // Publish assets if any
        $this->publishAssets();
        
        $this->info('{NAME} installed successfully!');
    }

    public function uninstall(): void
    {
        parent::uninstall();
        
        $this->info('Uninstalling {NAME}...');
        
        // Rollback migrations
        if ($this->migrations()) {
            $this->rollbackMigrations();
        }
        
        // Remove published assets
        $this->removeAssets();
        
        $this->info('{NAME} uninstalled successfully!');
    }

    public function activate(): void
    {
        parent::activate();
        
        $this->info('{NAME} activated!');
        
        // Sidebar menu is automatically registered by the plugin system
        
        // Ensure assets are published
        $this->publishAssets();
    }

    public function deactivate(): void
    {
        parent::deactivate();
        
        $this->info('{NAME} deactivated!');
        
        // Sidebar menu is automatically removed by the plugin system
    }
    
    /**
     * Run plugin migrations
     */
    protected function runMigrations(): void
    {
        $migrationsPath = $this->migrations();
        
        if ($migrationsPath && file_exists($migrationsPath)) {
            \Illuminate\Support\Facades\Artisan::call('migrate', [
                '--path' => str_replace(base_path() . '/', '', $migrationsPath),
                '--force' => true,
            ]);
            $this->info('Migrations executed!');
        }
    }
    
    /**
     * Rollback plugin migrations
     */
    protected function rollbackMigrations(): void
    {
        $migrationsPath = $this->migrations();
        
        if ($migrationsPath && file_exists($migrationsPath)) {
            \Illuminate\Support\Facades\Artisan::call('migrate:rollback', [
                '--path' => str_replace(base_path() . '/', '', $migrationsPath),
                '--force' => true,
            ]);
            $this->info('Migrations rolled back!');
        }
    }
    
    /**
     * Publish plugin assets
     */
    protected function publishAssets(): void
    {
        $assetsPath = __DIR__ . '/../resources/assets';
        $publicPath = public_path('vendor/hyro-plugins/{ID}');
        
        if (file_exists($assetsPath)) {
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            
            // Copy assets recursively
            $this->copyDirectory($assetsPath, $publicPath);
            $this->info('Assets published!');
        }
    }
    
    /**
     * Remove published assets
     */
    protected function removeAssets(): void
    {
        $publicPath = public_path('vendor/hyro-plugins/{ID}');
        
        if (file_exists($publicPath)) {
            $this->deleteDirectory($publicPath);
            $this->info('Assets removed!');
        }
    }
    
    /**
     * Copy directory recursively
     */
    protected function copyDirectory($source, $destination): void
    {
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!file_exists($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item, $target);
            }
        }
    }
    
    /**
     * Delete directory recursively
     */
    protected function deleteDirectory($directory): void
    {
        if (!file_exists($directory)) {
            return;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item);
            } else {
                unlink($item);
            }
        }
        
        rmdir($directory);
    }
}
PHP;
        
        // Replace placeholders
        return str_replace(
            ['{NAME}', '{ID}', '{AUTHOR}', '{ROUTE_PREFIX}'],
            [$name, $id, $author, $routePrefix],
            $stub
        );
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
    ->middleware(['web'])
    ->name('hyro.plugin.{$id}.')
    ->group(function () {
        Route::get('/', function () {
            return view('hyro-plugin-{$id}::index');
        })->name('index');

        // Add more routes here
        // To protect routes with authentication, add ->middleware('hyro.auth')
    });
PHP;
    }

    protected function getPluginId($name)
    {
        return Str::kebab($name);
    }

    protected function getIndexViewStub($name)
    {
        $pluginId = $this->getPluginId($name);
        
        $stub = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{NAME} Plugin - Hyro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .plugin-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        h1 {
            color: #1a202c;
            font-size: 28px;
            font-weight: 700;
        }

        .subtitle {
            color: #718096;
            font-size: 14px;
            margin-top: 4px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .icon-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .icon-blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .icon-green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .icon-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

        .card h2 {
            color: #1a202c;
            font-size: 20px;
            font-weight: 600;
        }

        .card p {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .card ul {
            list-style: none;
            margin: 16px 0;
        }

        .card li {
            color: #4a5568;
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
        }

        .card li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #48bb78;
            font-weight: bold;
        }

        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 12px 0;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: #edf2f7;
            color: #4a5568;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }

        .demo-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .demo-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .demo-app {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .demo-card {
            background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%);
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .demo-card:hover {
            border-color: #667eea;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .demo-card-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .demo-card h3 {
            color: #1a202c;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .demo-card p {
            color: #718096;
            font-size: 14px;
        }

        .alert {
            background: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
        }

        .alert-title {
            color: #2c5282;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .alert-text {
            color: #2d3748;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-title">
                <div class="plugin-icon">🔌</div>
                <div>
                    <h1>{NAME} Plugin</h1>
                    <p class="subtitle">Welcome to your new Hyro plugin!</p>
                </div>
            </div>
            <a href="/hyro/dashboard" class="back-btn">
                <span>←</span>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Info Cards -->
        <div class="grid">
            <!-- Quick Start Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-purple">🚀</div>
                    <h2>Quick Start</h2>
                </div>
                <p>Your plugin has been successfully created! Here's what you need to know:</p>
                <ul>
                    <li>Plugin ID: <strong>{PLUGIN_ID}</strong></li>
                    <li>Location: <code>hyro-plugins/{PLUGIN_ID}/</code></li>
                    <li>Main file: <code>src/Plugin.php</code></li>
                    <li>View file: <code>resources/views/index.blade.php</code></li>
                </ul>
            </div>

            <!-- Development Guide Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-blue">📚</div>
                    <h2>Development Guide</h2>
                </div>
                <p>Follow these steps to develop your plugin:</p>
                <ul>
                    <li>Edit <code>src/Plugin.php</code> for logic</li>
                    <li>Add routes in <code>routes/web.php</code></li>
                    <li>Create views in <code>resources/views/</code></li>
                    <li>Add migrations in <code>database/migrations/</code></li>
                </ul>
            </div>

            <!-- Commands Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-green">⚡</div>
                    <h2>Useful Commands</h2>
                </div>
                <p>Manage your plugin with these commands:</p>
                <div class="code-block">
# Install plugin<br>
php artisan hyro:plugin:install {PLUGIN_ID}<br><br>
# Activate plugin<br>
php artisan hyro:plugin:activate {PLUGIN_ID}<br><br>
# Deactivate plugin<br>
php artisan hyro:plugin:deactivate {PLUGIN_ID}
                </div>
            </div>

            <!-- Features Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-orange">✨</div>
                    <h2>Available Features</h2>
                </div>
                <p>Your plugin comes with these features:</p>
                <ul>
                    <li>Automatic migration management</li>
                    <li>Asset publishing system</li>
                    <li>Sidebar menu integration</li>
                    <li>Lifecycle hooks (install/uninstall)</li>
                </ul>
            </div>
        </div>

        <!-- Demo Application -->
        <div class="demo-section">
            <div class="demo-header">
                <div class="card-icon icon-purple">🎨</div>
                <div>
                    <h2>Demo Application</h2>
                    <p class="subtitle">Interactive examples to get you started</p>
                </div>
            </div>

            <div class="alert">
                <div class="alert-title">💡 Getting Started</div>
                <div class="alert-text">
                    This is a demo section. Replace this content with your plugin's functionality. 
                    Edit <code>resources/views/index.blade.php</code> to customize this page.
                </div>
            </div>

            <div class="demo-app">
                <div class="demo-card">
                    <div class="demo-card-icon">📝</div>
                    <h3>Create</h3>
                    <p>Add new items to your plugin</p>
                </div>

                <div class="demo-card">
                    <div class="demo-card-icon">📋</div>
                    <h3>List</h3>
                    <p>View all your plugin data</p>
                </div>

                <div class="demo-card">
                    <div class="demo-card-icon">✏️</div>
                    <h3>Edit</h3>
                    <p>Modify existing items</p>
                </div>

                <div class="demo-card">
                    <div class="demo-card-icon">🗑️</div>
                    <h3>Delete</h3>
                    <p>Remove unwanted items</p>
                </div>
            </div>
        </div>

        <!-- Development Instructions -->
        <div class="card" style="margin-top: 24px;">
            <div class="card-header">
                <div class="card-icon icon-blue">📖</div>
                <h2>Plugin Development Instructions</h2>
            </div>
            
            <h3 style="color: #2d3748; margin: 20px 0 12px 0; font-size: 18px;">1. Define Your Plugin Logic</h3>
            <p>Open <code>src/Plugin.php</code> and implement your plugin methods:</p>
            <div class="code-block">
public function boot(): void<br>
{<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Initialize your plugin<br>
&nbsp;&nbsp;&nbsp;&nbsp;$this->loadViewsFrom(__DIR__.'/../resources/views', 'plugin-{PLUGIN_ID}');<br>
}
            </div>

            <h3 style="color: #2d3748; margin: 20px 0 12px 0; font-size: 18px;">2. Add Routes</h3>
            <p>Edit <code>routes/web.php</code> to add your plugin routes:</p>
            <div class="code-block">
Route::get('/list', function () {<br>
&nbsp;&nbsp;&nbsp;&nbsp;return view('hyro-plugin-{PLUGIN_ID}::list');<br>
})->name('list');
            </div>

            <h3 style="color: #2d3748; margin: 20px 0 12px 0; font-size: 18px;">3. Create Database Tables</h3>
            <p>Add migrations in <code>database/migrations/</code>:</p>
            <div class="code-block">
php artisan make:migration create_your_table --path=hyro-plugins/{PLUGIN_ID}/database/migrations
            </div>

            <h3 style="color: #2d3748; margin: 20px 0 12px 0; font-size: 18px;">4. Build Your Views</h3>
            <p>Create Blade templates in <code>resources/views/</code> directory.</p>

            <div class="alert" style="margin-top: 20px;">
                <div class="alert-title">📚 Documentation</div>
                <div class="alert-text">
                    For complete documentation, visit the Hyro documentation or check the README.md file in your plugin directory.
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add interactivity to demo cards
        document.querySelectorAll('.demo-card').forEach(card => {
            card.addEventListener('click', function() {
                const action = this.querySelector('h3').textContent;
                alert(`${action} functionality - Implement your logic here!\n\nEdit resources/views/index.blade.php to add real functionality.`);
            });
        });
    </script>
</body>
</html>
HTML;
        
        // Replace placeholders
        return str_replace(
            ['{NAME}', '{PLUGIN_ID}'],
            [$name, $pluginId],
            $stub
        );
    }
}

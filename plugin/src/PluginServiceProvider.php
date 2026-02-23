<?php

namespace Marufsharia\Hyro\Plugin;

use Illuminate\Support\ServiceProvider;
use Marufsharia\Hyro\Plugin\Support\Plugins\PluginManager;
use Marufsharia\Hyro\Plugin\Support\Hooks\HookManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Plugin Manager
        $this->app->singleton('hyro.plugins', function ($app) {
            return new PluginManager($app);
        });

        // Register Hook Manager
        $this->app->singleton(HookManager::class, function ($app) {
            return new HookManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
            $this->registerCommands();
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Boot plugins
        $this->bootPlugins();
    }

    /**
     * Publish package resources.
     */
    private function publishResources(): void
    {
        // Plugin stubs
        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('stubs/hyro/plugin'),
        ], 'hyro-plugin-stubs');
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            // Plugin commands
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginListCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginMakeCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginInstallCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginUninstallCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginActivateCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginDeactivateCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginMarketplaceCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginUpgradeCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Plugin\PluginInstallRemoteCommand::class,
            
            // Hook commands
            \Marufsharia\Hyro\Plugin\Console\Commands\Hooks\ListHooksCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Hooks\ShowHookCommand::class,
            \Marufsharia\Hyro\Plugin\Console\Commands\Hooks\ClearHooksCommand::class,
        ]);
    }

    /**
     * Boot plugins.
     */
    protected function bootPlugins(): void
    {
        if (!config('hyro.plugins.enabled', true)) {
            return;
        }

        try {
            $pluginManager = $this->app->make('hyro.plugins');
            $pluginManager->load();
        } catch (\Exception $e) {
            if ($this->app->runningInConsole()) {
                $this->app['log']->warning('Hyro Plugin: Could not load plugins: ' . $e->getMessage());
            }
        }
    }
}

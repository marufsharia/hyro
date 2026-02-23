<?php

namespace Marufsharia\Hyro\Crud;

use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register CRUD services
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\SmartCrudRouteManager::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\PermissionGenerator::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\CrudRouteAutoDiscoverer::class);
        
        // Register CRUD generation services
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\CodeGeneratorService::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\CrudConfigurationService::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\FieldParserService::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\StubManagerService::class);
        
        // Register CRUD generators
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\Generators\ComponentGenerator::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\Generators\MigrationGenerator::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\Generators\ModelGenerator::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\Generators\RouteGenerator::class);
        $this->app->singleton(\Marufsharia\Hyro\Crud\Services\Crud\Generators\ViewGenerator::class);
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

        // Load CRUD routes
        $this->loadSmartCrudRoutes();
    }

    /**
     * Publish package resources.
     */
    private function publishResources(): void
    {
        // CRUD stubs
        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('stubs/hyro/crud'),
        ], 'hyro-crud-stubs');
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            \Marufsharia\Hyro\Crud\Console\Commands\Crud\MakeCrudCommand::class,
            \Marufsharia\Hyro\Crud\Console\Commands\Crud\HyroModuleCommand::class,
            \Marufsharia\Hyro\Crud\Console\Commands\Crud\DiscoverCrudRoutesCommand::class,
            \Marufsharia\Hyro\Crud\Console\Commands\RouteBackupCommand::class,
        ]);
    }

    /**
     * Load CRUD routes from application root.
     */
    private function loadSmartCrudRoutes(): void
    {
        $crudRouteFile = base_path('routes/hyro/crud.php');

        if (file_exists($crudRouteFile)) {
            $this->loadRoutesFrom($crudRouteFile);
        }
    }
}

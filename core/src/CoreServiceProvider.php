<?php

namespace Marufsharia\Hyro\Core;

use Illuminate\Support\ServiceProvider;
use Marufsharia\Hyro\Core\Contracts\AuthorizationResolverContract;
use Marufsharia\Hyro\Core\Contracts\CacheInvalidatorContract;
use Marufsharia\Hyro\Core\Contracts\HyroUserContract;
use Marufsharia\Hyro\Core\Services\AuthorizationService;
use Marufsharia\Hyro\Core\Services\CacheInvalidator;
use Marufsharia\Hyro\Core\Services\GateRegistrar;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/hyro.php', 'hyro');

        // Bind core contracts
        $this->app->bind(
            HyroUserContract::class,
            config('hyro.database.models.users', \App\Models\User::class)
        );

        // Register core services
        $this->app->singleton(AuthorizationResolverContract::class, AuthorizationService::class);
        $this->app->singleton(CacheInvalidatorContract::class, CacheInvalidator::class);
        $this->app->singleton(GateRegistrar::class);

        // Register HyroManager
        $this->app->singleton('hyro', function ($app) {
            return new \Marufsharia\Hyro\Core\HyroManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }

        // Load migrations
        if (config('hyro.database.migrations.autoload', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // Register authorization gates
        $gateRegistrar = $this->app->make(GateRegistrar::class);
        $gateRegistrar->register();
    }

    /**
     * Publish package resources.
     */
    private function publishResources(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../config/hyro.php' => config_path('hyro.php'),
        ], 'hyro-core-config');

        // Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations/hyro'),
        ], 'hyro-core-migrations');

        // Models (for customization)
        $this->publishes([
            __DIR__ . '/Models' => app_path('Models/Hyro'),
        ], 'hyro-core-models');
    }
}


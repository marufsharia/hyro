<?php

namespace Marufsharia\Hyro\Auth;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register auth services
        $this->app->singleton(\Marufsharia\Hyro\Auth\Services\TokenSynchronizationService::class);
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

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/auth.php');
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            // Token commands
            \Marufsharia\Hyro\Auth\Console\Commands\Token\SyncTokensCommand::class,
            \Marufsharia\Hyro\Auth\Console\Commands\Token\RevokeTokensCommand::class,
            
            // Emergency commands
            \Marufsharia\Hyro\Auth\Console\Commands\Emergency\LockdownCommand::class,
            \Marufsharia\Hyro\Auth\Console\Commands\Emergency\UnlockdownCommand::class,
            \Marufsharia\Hyro\Auth\Console\Commands\Emergency\RevokeAllTokensCommand::class,
        ]);
    }

    /**
     * Publish package resources.
     */
    private function publishResources(): void
    {
        // Routes
        $this->publishes([
            __DIR__ . '/../routes/auth.php' => base_path('routes/hyro/auth.php'),
        ], 'hyro-auth-routes');

        // Middleware
        $this->publishes([
            __DIR__ . '/Http/Middleware' => app_path('Http/Middleware/Hyro/Auth'),
        ], 'hyro-auth-middleware');

        // Controllers
        $this->publishes([
            __DIR__ . '/Http/Controllers' => app_path('Http/Controllers/Hyro/Auth'),
        ], 'hyro-auth-controllers');
    }
}

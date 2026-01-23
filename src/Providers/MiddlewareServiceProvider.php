<?php

namespace Marufsharia\Hyro\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Marufsharia\Hyro\Http\Middleware\AuditRequest;
use Marufsharia\Hyro\Http\Middleware\EnsureHasAbility;
use Marufsharia\Hyro\Http\Middleware\EnsureHasAnyPrivilege;
use Marufsharia\Hyro\Http\Middleware\EnsureHasAnyRole;
use Marufsharia\Hyro\Http\Middleware\EnsureHasPrivilege;
use Marufsharia\Hyro\Http\Middleware\EnsureHasRole;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * The middleware aliases.
     */
    protected array $middlewareAliases = [
        'hyro.role' => EnsureHasRole::class,
        'hyro.roles' => EnsureHasAnyRole::class,
        'hyro.privilege' => EnsureHasPrivilege::class,
        'hyro.privileges' => EnsureHasAnyPrivilege::class,
        'hyro.ability' => EnsureHasAbility::class,
        'hyro.audit' => AuditRequest::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register middleware aliases
        foreach ($this->middlewareAliases as $alias => $middleware) {
            $this->app->singleton($middleware);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $kernel = $this->app->make(Kernel::class);

        // Register middleware aliases for use in routes
        foreach ($this->middlewareAliases as $alias => $middleware) {
            $kernel->aliasMiddleware($alias, $middleware);
        }

        // Optionally push global middleware
        $this->registerGlobalMiddleware($kernel);
    }

    /**
     * Register global middleware.
     */
    private function registerGlobalMiddleware(Kernel $kernel): void
    {
        // Add audit middleware globally if configured
        if (config('hyro.auditing.middleware.global', false)) {
            $kernel->pushMiddleware(AuditRequest::class);
        }

        // Add CORS headers for API responses
        $kernel->pushMiddleware(\Illuminate\Http\Middleware\HandleCors::class);
    }
}

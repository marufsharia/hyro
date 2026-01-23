<?php

namespace Marufsharia\Hyro\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Marufsharia\Hyro\Http\Middleware\EnsureApiEnabled;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register API services.
     */
    public function register(): void
    {
        // Only register if API is enabled
        if (!Config::get('hyro.api.enabled', false)) {
            return;
        }

        // Register API-specific bindings
        $this->registerApiBindings();
    }

    /**
     * Bootstrap API services.
     */
    public function boot(): void
    {
        // Only boot if API is enabled
        if (!Config::get('hyro.api.enabled', false)) {
            return;
        }

        $this->registerRoutes();
        $this->registerMiddleware();
        $this->registerRateLimiting();
    }

    /**
     * Register API-specific bindings.
     */
    private function registerApiBindings(): void
    {
        // API-specific services can be bound here
        $this->app->singleton('hyro.api.throttle', function ($app) {
            return $app['cache']->store(Config::get('hyro.api.rate_limit.store', 'redis'));
        });
    }

    /**
     * Register API routes.
     */
    private function registerRoutes(): void
    {
        Route::prefix(Config::get('hyro.api.prefix', 'api/hyro'))
            ->middleware(Config::get('hyro.api.middleware', ['api', 'auth:sanctum']))
            ->name('hyro.api.')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
            });
    }

    /**
     * Register API middleware.
     */
    private function registerMiddleware(): void
    {
        // Ensure API is enabled middleware
        $this->app['router']->aliasMiddleware('hyro.api.enabled', EnsureApiEnabled::class);

        // Rate limiting middleware
        $this->app['router']->aliasMiddleware('hyro.api.throttle', \Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    /**
     * Configure rate limiting.
     */
    private function registerRateLimiting(): void
    {
        // Configure rate limiting based on config
        $maxAttempts = Config::get('hyro.api.rate_limit.max_attempts', 60);
        $decayMinutes = Config::get('hyro.api.rate_limit.decay_minutes', 1);

        // Register rate limiter
        $this->app['rateLimiter']->for('hyro-api', function ($request) use ($maxAttempts, $decayMinutes) {
            $key = $request->user()
                ? 'hyro-api:' . $request->user()->id
                : 'hyro-api:' . $request->ip();

            return \Illuminate\Cache\RateLimiting\Limit::perMinutes($decayMinutes, $maxAttempts)
                ->by($key)
                ->response(function ($request, $headers) {
                    return response()->json([
                        'error' => [
                            'code' => 'rate_limit_exceeded',
                            'message' => 'Too many requests',
                            'retry_after' => $headers['Retry-After'],
                        ]
                    ], 429);
                });
        });
    }
}

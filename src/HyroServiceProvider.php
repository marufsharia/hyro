<?php

namespace Marufsharia\Hyro;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;
use Marufsharia\Hyro\Contracts\CacheInvalidatorContract;
use Marufsharia\Hyro\Contracts\HyroUserContract;
use Marufsharia\Hyro\Events\PrivilegeGranted;
use Marufsharia\Hyro\Events\PrivilegeRevoked;
use Marufsharia\Hyro\Events\RoleAssigned;
use Marufsharia\Hyro\Events\RoleRevoked;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Marufsharia\Hyro\Listeners\TokenSynchronizationListener;
use Marufsharia\Hyro\Providers\ApiServiceProvider;
use Marufsharia\Hyro\Providers\BladeDirectivesServiceProvider;
use Marufsharia\Hyro\Providers\EventServiceProvider;
use Marufsharia\Hyro\Services\AuthorizationService;
use Marufsharia\Hyro\Services\CacheInvalidator;
use Marufsharia\Hyro\Services\GateRegistrar;
use Marufsharia\Hyro\Services\TokenSynchronizationService;

class HyroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * No heavy logic here - only bindings.
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../config/hyro.php', 'hyro');

        // Bind core contracts
        $this->bindCoreContracts();

        $this->app->singleton(AuthorizationResolverContract::class, AuthorizationService::class);
        $this->app->singleton(CacheInvalidatorContract::class, CacheInvalidator::class);
        $this->app->singleton(GateRegistrar::class);
        $this->app->singleton(TokenSynchronizationService::class);

        if (Config::get('hyro.api.enabled', false)) {
            $this->app->register(ApiServiceProvider::class);
        }

        $this->app->singleton(\Marufsharia\Hyro\Blade\HyroBladeHelper::class);

    }

    /**
     * Bootstrap services.
     * All boot logic goes here.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
            $this->registerCommands();
        }


        $this->loadConditionalResources();
        $this->registerBladeDirectives();
        $this->registerMacros();
        $this->registerAuthorization();
        $this->registerEventListeners();

        // Register event service provider
        $this->app->register(EventServiceProvider::class);

        if (Config::get('hyro.api.enabled', false) && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../routes/api.php' => base_path('routes/hyro-api.php'),
            ], 'hyro-api-routes');
        }

        if (Config::get('hyro.ui.enabled', false)) {
            $this->app->register(BladeDirectivesServiceProvider::class);

            // Load views
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hyro');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/hyro'),
            ], 'hyro-views');
        }
    }

    /**
     * Bind core contracts to implementations.
     */
    private function bindCoreContracts(): void
    {
        $this->app->bind(
            HyroUserContract::class,
            config('hyro.models.user', \App\Models\User::class)
        );

        // Additional bindings will be added in Phase 3
    }

    /**
     * Publish package resources.
     */
    private function publishResources(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../config/hyro.php' => config_path('hyro.php'),
        ], 'hyro-config');

        // Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'hyro-migrations');

        // Publish events and listeners
        $this->publishes([
            __DIR__ . '/../Events/' => app_path('Events/Hyro'),
            __DIR__ . '/../Listeners/' => app_path('Listeners/Hyro'),
        ], 'hyro-events');
        // Views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/hyro'),
        ], 'hyro-views');

        // Translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/hyro'),
        ], 'hyro-translations');

        // Publish compiled assets
//        $this->publishes([
//            __DIR__ . '/../public/css' => public_path('vendor/hyro/css'),
//            __DIR__ . '/../public/js' => public_path('vendor/hyro/js'),
//            __DIR__ . '/../public/images' => public_path('vendor/hyro/images'),
//        ], 'hyro-assets');

        $this->publishes([
            __DIR__ . '/../public/build' => public_path('vendor/hyro'),
            __DIR__ . '/../public/images' => public_path('vendor/hyro/images'),
        ], 'hyro-assets');

        // Publish source assets for development
//        $this->publishes([
//            __DIR__ . '/../resources/css' => resource_path('vendor/hyro/css'),
//            __DIR__ . '/../resources/js' => resource_path('vendor/hyro/js'),
//        ], 'hyro-source-assets');


    }

    /**
     * Conditionally load resources based on config.
     */
    private function loadConditionalResources(): void
    {
        // Migrations
        if (config('hyro.database.migrations.autoload', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // Routes
        if (config('hyro.api.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        if (config('hyro.ui.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            $this->loadRoutesFrom(__DIR__ . '/../routes/auth.php');
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hyro');

        }
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            // User Commands
            \Marufsharia\Hyro\Console\Commands\User\AssignRoleCommand::class,
            \Marufsharia\Hyro\Console\Commands\User\SuspendCommand::class,
            \Marufsharia\Hyro\Console\Commands\User\ListRolesCommand::class,
            \Marufsharia\Hyro\Console\Commands\User\ListPrivilegesCommand::class,

            // Role Commands
            \Marufsharia\Hyro\Console\Commands\Role\CreateRoleCommand::class,
            \Marufsharia\Hyro\Console\Commands\Role\DeleteRoleCommand::class,
            \Marufsharia\Hyro\Console\Commands\Role\ListRolesCommand::class,
            \Marufsharia\Hyro\Console\Commands\Role\GrantPrivilegeCommand::class,
            \Marufsharia\Hyro\Console\Commands\Role\RevokePrivilegeCommand::class,

            // Privilege Commands
            \Marufsharia\Hyro\Console\Commands\Privilege\CreatePrivilegeCommand::class,
            \Marufsharia\Hyro\Console\Commands\Privilege\DeletePrivilegeCommand::class,
            \Marufsharia\Hyro\Console\Commands\Privilege\ListPrivilegesCommand::class,

            // Token Commands
            \Marufsharia\Hyro\Console\Commands\Token\SyncTokensCommand::class,
            \Marufsharia\Hyro\Console\Commands\Token\RevokeTokensCommand::class,

            // Emergency Commands
            \Marufsharia\Hyro\Console\Commands\Emergency\RevokeAllTokensCommand::class,
            \Marufsharia\Hyro\Console\Commands\Emergency\LockdownCommand::class,
            \Marufsharia\Hyro\Console\Commands\Emergency\UnlockdownCommand::class,


            // Setup & Maintenance
            \Marufsharia\Hyro\Console\Commands\Setup\InstallCommand::class,

            // Maintenance
            \Marufsharia\Hyro\Console\Commands\Maintenance\HealthCheckCommand::class,
            \Marufsharia\Hyro\Console\Commands\Maintenance\StatusCommand::class,
            \Marufsharia\Hyro\Console\Commands\Maintenance\CleanupCommand::class,
            // Publish Assets
            //  \Marufsharia\Hyro\Console\Commands\Publis\PublishHyroAssets::class,
            // \Marufsharia\Hyro\Console\Commands\Publis\HyroCompileCommand::class,
            // \Marufsharia\Hyro\Console\Commands\Publis\HyroPublishCommand::class,
            // \Marufsharia\Hyro\Console\Commands\Publis\ViteCompileCommand::class,
            //  \Marufsharia\Hyro\Console\Commands\Publis\ViteDevCommand::class,
        ]);
    }

    /**
     * Register Blade directives.
     */
    private function registerBladeDirectives(): void
    {

        \Blade::directive('hyroAssets', function () {
            return '<?php echo \Marufsharia\Hyro\Helpers\HyroAsset::tags(); ?>';
        });

        /*  \Blade::directive('hyroCss', function () {
              return '<?php echo \Marufsharia\Hyro\Helpers\HyroAsset::css(); ?>';
          });

          \Blade::directive('hyroJs', function () {
              return '<?php echo \Marufsharia\Hyro\Helpers\HyroAsset::js(); ?>';
          });*/


        // Register Blade components
        \Blade::component('hyro::components.card', 'hyro-card');
        \Blade::component('hyro::components.button', 'hyro-button');
        \Blade::component('hyro::components.alert', 'hyro-alert');
        \Blade::component('hyro::components.form', 'hyro-form');
        \Blade::component('hyro::components.table', 'hyro-table');
    }

    /**
     * Register custom macros.
     */
    private function registerMacros(): void
    {
        // Macros will be added in later phases
    }

    /**
     * Register Authorization.
     */
    private function registerAuthorization(): void
    {
        $gateRegistrar = $this->app->make(GateRegistrar::class);
        $gateRegistrar->register();
    }

    /**
     * Register EventListeners.
     */
    private function registerEventListeners(): void
    {
        if (Config::get('hyro.tokens.synchronization.enabled', true)) {
            Event::listen(RoleAssigned::class, [TokenSynchronizationListener::class, 'handleRoleAssigned']);
            Event::listen(RoleRevoked::class, [TokenSynchronizationListener::class, 'handleRoleRevoked']);
            Event::listen(PrivilegeGranted::class, [TokenSynchronizationListener::class, 'handlePrivilegeGranted']);
            Event::listen(PrivilegeRevoked::class, [TokenSynchronizationListener::class, 'handlePrivilegeRevoked']);
            Event::listen(UserSuspended::class, [TokenSynchronizationListener::class, 'handleUserSuspended']);
            Event::listen(UserUnsuspended::class, [TokenSynchronizationListener::class, 'handleUserUnsuspended']);
        }
        // Register audit logging for all events
        $this->registerAuditLogging();
    }

    /**
     * Register audit logging for events.
     */
    protected function registerAuditLogging(): void
    {
        $events = [
            RoleAssigned::class,
            RoleRevoked::class,
            PrivilegeGranted::class,
            PrivilegeRevoked::class,
            UserSuspended::class,
            UserUnsuspended::class,
        ];

        foreach ($events as $event) {
            Event::listen($event, function ($eventInstance) {
                // Log to audit log
                $this->logToAudit($eventInstance);
            });
        }
    }

    /**
     * Log event to audit log.
     */
    protected function logToAudit($event): void
    {
        // This would be implemented based on your audit logging system
        // Example:
        // AuditLog::create([
        //     'action' => $this->getEventAction($event),
        //     'user_id' => $this->getEventUserId($event),
        //     'details' => $this->getEventDetails($event),
        //     'ip_address' => request()->ip(),
        //     'user_agent' => request()->userAgent(),
        // ]);
    }


}

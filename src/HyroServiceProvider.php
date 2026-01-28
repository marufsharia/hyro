<?php

namespace Marufsharia\Hyro;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
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
use Marufsharia\Hyro\Support\Plugins\PluginManager;

class HyroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../config/hyro.php', 'hyro');

        // Bind core contracts
        $this->bindCoreContracts();

        // Register core services
        $this->app->singleton(AuthorizationResolverContract::class, AuthorizationService::class);
        $this->app->singleton(CacheInvalidatorContract::class, CacheInvalidator::class);
        $this->app->singleton(GateRegistrar::class);
        $this->app->singleton(TokenSynchronizationService::class);
        $this->app->singleton(\Marufsharia\Hyro\Blade\HyroBladeHelper::class);

        // Register Plugin Manager
        $this->app->singleton('hyro.plugins', function ($app) {
            return new PluginManager($app);
        });

        // Register API provider if enabled
        if (Config::get('hyro.api.enabled', false)) {
            $this->app->register(ApiServiceProvider::class);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
            $this->registerCommands();
        }

        // Register middleware
        $router->aliasMiddleware('hyro.auth', \Marufsharia\Hyro\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('hyro.guest', \Marufsharia\Hyro\Http\Middleware\RedirectIfAuthenticated::class);

        // Load conditional resources
        $this->loadConditionalResources();

        // Register components
        $this->registerBladeDirectives();
        $this->registerLivewireComponents();
        $this->registerMacros();
        $this->registerAuthorization();
        $this->registerEventListeners();

        // Auto-add trait to User model
        $this->addTraitToUserModel();

        // Register service providers
        $this->app->register(EventServiceProvider::class);

        if (Config::get('hyro.admin.enabled', false)) {
            $this->app->register(BladeDirectivesServiceProvider::class);
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hyro');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/hyro'),
            ], 'hyro-views');
        }

        // Boot plugins
        $this->bootPlugins();
    }

    /**
     * Bind core contracts to implementations.
     */
    private function bindCoreContracts(): void
    {
        $this->app->bind(
            HyroUserContract::class,
            config('hyro.database.models.users', \App\Models\User::class)
        );
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

        // Events and listeners
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

        // Compiled assets
        $this->publishes([
            __DIR__ . '/../public/build' => public_path('vendor/hyro'),
            __DIR__ . '/../public/images' => public_path('vendor/hyro/images'),
        ], 'hyro-assets');
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

        if (config('hyro.admin.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
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
            \Marufsharia\Hyro\Console\Commands\User\HyroListUsersCommand::class,
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
            \Marufsharia\Hyro\Console\Commands\Maintenance\HealthCheckCommand::class,
            \Marufsharia\Hyro\Console\Commands\Maintenance\StatusCommand::class,
            \Marufsharia\Hyro\Console\Commands\Maintenance\CleanupCommand::class,

            // CRUD Generator
            \Marufsharia\Hyro\Console\Commands\Crud\MakeCrudCommand::class,

            // Plugin Commands
            \Marufsharia\Hyro\Console\Commands\Plugin\PluginListCommand::class,
            \Marufsharia\Hyro\Console\Commands\Plugin\PluginMakeCommand::class,
            \Marufsharia\Hyro\Console\Commands\Plugin\PluginInstallCommand::class,
            \Marufsharia\Hyro\Console\Commands\Plugin\PluginUninstallCommand::class,
            \Marufsharia\Hyro\Console\Commands\Plugin\PluginActivateCommand::class,
            \Marufsharia\Hyro\Console\Commands\Plugin\PluginDeactivateCommand::class,
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

        \Blade::directive('hyroCss', function () {
            return '<?php echo \Marufsharia\Hyro\Helpers\HyroAsset::css(); ?>';
        });

        \Blade::directive('hyroJs', function () {
            return '<?php echo \Marufsharia\Hyro\Helpers\HyroAsset::js(); ?>';
        });

        // Register Blade components
        \Blade::component('hyro::components.card', 'hyro-card');
        \Blade::component('hyro::components.button', 'hyro-button');
        \Blade::component('hyro::components.alert', 'hyro-alert');
        \Blade::component('hyro::components.form', 'hyro-form');
        \Blade::component('hyro::components.table', 'hyro-table');
        \Blade::component('hyro::components.modal', 'hyro-modal');
    }

    /**
     * Register Livewire components.
     */
    private function registerLivewireComponents(): void
    {
        if (!config('hyro.livewire.enabled', true)) {
            return;
        }

        // Check if Livewire is installed
        if (!class_exists(\Livewire\Livewire::class)) {
            return;
        }

        try {
            // Register Livewire components
            \Livewire\Livewire::component('hyro.role-manager', \Marufsharia\Hyro\Livewire\Admin\RoleManager::class);
            \Livewire\Livewire::component('hyro.user-manager', \Marufsharia\Hyro\Livewire\Admin\UserManager::class);
            \Livewire\Livewire::component('hyro.privilege-manager', \Marufsharia\Hyro\Livewire\Admin\PrivilegeManager::class);
        } catch (\Exception $e) {
            // Silently fail if Livewire components can't be registered
            if ($this->app->runningInConsole()) {
                $this->app['log']->warning('Hyro: Could not register Livewire components. Make sure Livewire is installed: composer require livewire/livewire');
            }
        }
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
        if (Config::get('hyro.tokens.sync.enabled', true)) {
            Event::listen(RoleAssigned::class, [TokenSynchronizationListener::class, 'handleRoleAssigned']);
            Event::listen(RoleRevoked::class, [TokenSynchronizationListener::class, 'handleRoleRevoked']);
            Event::listen(PrivilegeGranted::class, [TokenSynchronizationListener::class, 'handlePrivilegeGranted']);
            Event::listen(PrivilegeRevoked::class, [TokenSynchronizationListener::class, 'handlePrivilegeRevoked']);
            Event::listen(UserSuspended::class, [TokenSynchronizationListener::class, 'handleUserSuspended']);
            Event::listen(UserUnsuspended::class, [TokenSynchronizationListener::class, 'handleUserUnsuspended']);
        }

        // Register audit logging
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
                $this->logToAudit($eventInstance);
            });
        }
    }

    /**
     * Log event to audit log.
     */
    protected function logToAudit($event): void
    {
        // Audit logging implementation
    }

    /**
     * Add HasHyroFeatures trait to User model.
     */
    protected function addTraitToUserModel(): void
    {
        $userModelPath = app_path('Models/User.php');

        if (!File::exists($userModelPath)) {
            return;
        }

        $content = File::get($userModelPath);

        if (str_contains($content, 'HasHyroFeatures')) {
            return;
        }

        if (!str_contains($content, 'use Marufsharia\Hyro\Traits\HasHyroFeatures;')) {
            $content = preg_replace(
                '/namespace App\\\\Models;/',
                "namespace App\\Models;\n\nuse Marufsharia\\Hyro\\Traits\\HasHyroFeatures;",
                $content
            );
        }

        $content = preg_replace(
            '/class User extends Authenticatable\s*\{/',
            "class User extends Authenticatable\n{\n    use HasHyroFeatures;",
            $content
        );

        File::put($userModelPath, $content);
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
            $pluginManager->discover();
            $pluginManager->load();
        } catch (\Exception $e) {
            if ($this->app->runningInConsole()) {
                $this->app['log']->warning('Hyro: Could not load plugins: ' . $e->getMessage());
            }
        }
    }
}

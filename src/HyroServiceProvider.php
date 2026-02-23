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
use Marufsharia\Hyro\Services\AuthorizationService;
use Marufsharia\Hyro\Services\CacheInvalidator;
use Marufsharia\Hyro\Services\GateRegistrar;
use Marufsharia\Hyro\Services\TokenSynchronizationService;
use Marufsharia\Hyro\Support\Plugins\PluginManager;
use Marufsharia\Hyro\Support\Hooks\HookManager;

class HyroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the bridge service provider first to load modular packages
        $this->app->register(\Marufsharia\Hyro\HyroBridgeServiceProvider::class);

        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../config/hyro.php', 'hyro');

        // Note: Helper functions are now loaded by the core package

        // Bind core contracts
        $this->bindCoreContracts();

        // Register HyroManager as singleton
        $this->app->singleton('hyro', function ($app) {
            return new \MarufSharia\Hyro\HyroManager($app);
        });

        // Note: Core services are now registered by modular packages via HyroBridgeServiceProvider
        // Keeping these for backward compatibility if modular packages are not loaded
        if (!class_exists(\Marufsharia\Hyro\Core\CoreServiceProvider::class)) {
            $this->app->singleton(AuthorizationResolverContract::class, AuthorizationService::class);
            $this->app->singleton(CacheInvalidatorContract::class, CacheInvalidator::class);
            $this->app->singleton(GateRegistrar::class);
            $this->app->singleton(TokenSynchronizationService::class);
            $this->app->singleton(\Marufsharia\Hyro\Blade\HyroBladeHelper::class);
            $this->app->singleton(\Marufsharia\Hyro\Services\SmartCrudRouteManager::class);
            $this->app->singleton(\Marufsharia\Hyro\Services\SettingsService::class);

            // Register Plugin Manager
            $this->app->singleton('hyro.plugins', function ($app) {
                return new PluginManager($app);
            });

            // Register Hook Manager
            $this->app->singleton(HookManager::class, function ($app) {
                return new HookManager();
            });
        }

        // Register API provider if enabled (fallback if API package not loaded)
        if (Config::get('hyro.api.enabled', false) && !class_exists(\Marufsharia\Hyro\Api\ApiServiceProvider::class)) {
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

        // Apply runtime settings
        $this->applyRuntimeSettings();

        // Register middleware
        $router->aliasMiddleware('hyro.auth', \Marufsharia\Hyro\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('hyro.guest', \Marufsharia\Hyro\Http\Middleware\RedirectIfAuthenticated::class);
        $router->aliasMiddleware('hyro.2fa', \Marufsharia\Hyro\Http\Middleware\RequireTwoFactorAuth::class);

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
        // Note: Service providers are now handled by modular packages

        // Blade directives are now registered by AdminPanelServiceProvider

        // Smart CRUD route loading - always from application root
        $this->loadSmartCrudRoutes();
        
        // Boot plugins
        $this->bootPlugins();
    }

    /**
     * Load CRUD routes from application root.
     * CRUD routes are always loaded from routes/hyro/crud.php in the application.
     *
     * @return void
     */
    private function loadSmartCrudRoutes(): void
    {
        $crudRouteFile = base_path('routes/hyro/crud.php');

        if (File::exists($crudRouteFile)) {
            $this->loadRoutesFrom($crudRouteFile);
        }
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
     * Note: Most resources are now published by modular packages.
     */
    private function publishResources(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../config/hyro.php' => config_path('hyro.php'),
        ], 'hyro-config');

        // Note: Other resources are published by modular packages
        // - Migrations: published by CoreServiceProvider
        // - Views: published by AdminPanelServiceProvider
        // - Assets: published by AdminPanelServiceProvider
        // - Routes: published by respective packages
    }

    /**
     * Conditionally load resources based on config.
     */
    private function loadConditionalResources(): void
    {
        // Smart migration loading
        if (config('hyro.database.migrations.autoload', true)) {
            $this->loadSmartMigrations();
        }

        // Smart translation loading
        $this->loadSmartTranslations();

        // Note: Routes are now loaded by modular packages
        // - API routes: loaded by ApiServiceProvider
        // - Admin routes: loaded by AdminPanelServiceProvider
        // - Auth routes: loaded by AuthServiceProvider
        // - Profile routes: loaded by AdminPanelServiceProvider
        // - Notification routes: loaded by AdminPanelServiceProvider
    }

    /**
     * Load migrations from published location if exists, otherwise from package.
     * Note: Migrations are now loaded by CoreServiceProvider.
     *
     * @return void
     */
    private function loadSmartMigrations(): void
    {
        // Migrations are now loaded by CoreServiceProvider
    }

    /**
     * Load translations from published location if exists, otherwise from package.
     * Note: Translations are now loaded by respective modular packages.
     *
     * @return void
     */
    private function loadSmartTranslations(): void
    {
        // Translations are now loaded by modular packages
    }

    /**
     * Get the path to published assets if they exist, otherwise package assets.
     *
     * @param string $assetPath
     * @return string
     */
    public static function getAssetPath(string $assetPath = ''): string
    {
        $publishedAssets = public_path('vendor/hyro');
        $packageAssets = __DIR__ . '/../public/build';

        // Check if published assets exist
        if (File::exists($publishedAssets)) {
            return asset('vendor/hyro/' . ltrim($assetPath, '/'));
        }

        // Fallback to package assets (for development)
        return asset('vendor/hyro/' . ltrim($assetPath, '/'));
    }

    /**
     * Register console commands.
     * Note: Commands are now registered by modular packages.
     */
    private function registerCommands(): void
    {
        // Commands are now registered by their respective modular packages:
        // - Core commands: registered by CoreServiceProvider
        // - Plugin commands: registered by PluginServiceProvider
        // - Auth commands: registered by AuthServiceProvider
        // - CRUD commands: registered by CrudServiceProvider
        // - API commands: registered by ApiServiceProvider
    }

    /**
     * Register Blade directives.
     */
    private function registerBladeDirectives(): void
    {
        \Blade::directive('hyroAssets', function () {
            return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::tags(); ?>";
        });

        \Blade::directive('hyroCss', function () {
            return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::css(); ?>";
        });

        \Blade::directive('hyroJs', function () {
            return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::js(); ?>";
        });

        \Blade::directive('hyroImage', function ($expression) {
            return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::image({$expression}); ?>";
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
     * Note: Livewire components are now registered by modular packages.
     */
    private function registerLivewireComponents(): void
    {
        // Livewire components are now registered by their respective modular packages:
        // - Admin components: registered by AdminPanelServiceProvider
        // - CRUD components: registered by CrudServiceProvider
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

        if (!str_contains($content, 'use Marufsharia\Hyro\Core\Traits\HasHyroFeatures;')) {
            $content = preg_replace(
                '/namespace App\\\\Models;/',
                "namespace App\\Models;\n\nuse Marufsharia\\Hyro\\Core\\Traits\\HasHyroFeatures;",
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
            
            // Only load active plugins (fast - reads from state file + cache)
            $pluginManager->load();
            
        } catch (\Exception $e) {
            if ($this->app->runningInConsole()) {
                $this->app['log']->warning('Hyro: Could not load plugins: ' . $e->getMessage());
            }
        }
    }


    protected function isApplicationBoot(): bool
    {
        // Check if this is the main application boot, not a command execution
        return $this->app->isBooted() && !$this->app->runningInConsole();
    }

    /**
     * Apply runtime settings from database.
     */
    protected function applyRuntimeSettings(): void
    {
        try {
            // Apply locale setting
            $locale = hyro_config('locale', config('app.locale', 'en'));
            $this->app->setLocale($locale);

            // Apply timezone setting
            $timezone = hyro_config('timezone', config('app.timezone', 'UTC'));
            date_default_timezone_set($timezone);
            config(['app.timezone' => $timezone]);
        } catch (\Exception $e) {
            // Silently fail if settings table doesn't exist yet (during migration)
        }
    }
}

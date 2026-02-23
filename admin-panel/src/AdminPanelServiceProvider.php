<?php

namespace Marufsharia\Hyro\AdminPanel;

use Illuminate\Support\ServiceProvider;

class AdminPanelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register admin panel services
        $this->app->singleton(\Marufsharia\Hyro\AdminPanel\Services\SettingsService::class);
        $this->app->singleton(\Marufsharia\Hyro\AdminPanel\Services\SidebarRegistry::class);
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

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hyro');

        // Load routes
        if (config('hyro.admin.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
            $this->loadRoutesFrom(__DIR__ . '/../routes/notifications.php');
            $this->loadRoutesFrom(__DIR__ . '/../routes/profile.php');
        }

        // Register Livewire components
        $this->registerLivewireComponents();

        // Register Blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register console commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            \Marufsharia\Hyro\AdminPanel\Console\Commands\PublishAssetsCommand::class,
        ]);
    }

    /**
     * Publish package resources.
     */
    private function publishResources(): void
    {
        // Views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/hyro'),
        ], 'hyro-views');

        // Assets - publish to vendor/hyro (not vendor/hyro-admin)
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/hyro/css'),
            __DIR__ . '/../resources/js' => public_path('vendor/hyro/js'),
        ], 'hyro-assets');

        // Routes
        $this->publishes([
            __DIR__ . '/../routes/admin.php' => base_path('routes/hyro/admin.php'),
            __DIR__ . '/../routes/notifications.php' => base_path('routes/hyro/notifications.php'),
            __DIR__ . '/../routes/profile.php' => base_path('routes/hyro/profile.php'),
        ], 'hyro-routes');
    }

    /**
     * Register Livewire components.
     */
    private function registerLivewireComponents(): void
    {
        if (!class_exists(\Livewire\Livewire::class)) {
            return;
        }

        // Register admin components with hyro:: namespace
        \Livewire\Livewire::component('hyro::admin.sidebar', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\Sidebar::class);
        \Livewire\Livewire::component('hyro::admin.header', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\Header::class);
        \Livewire\Livewire::component('hyro::admin.dashboard', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\Dashboard::class);
        \Livewire\Livewire::component('hyro::admin.user-manager', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\UserManager::class);
        \Livewire\Livewire::component('hyro::admin.role-manager', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\RoleManager::class);
        \Livewire\Livewire::component('hyro::admin.privilege-manager', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\PrivilegeManager::class);
        \Livewire\Livewire::component('hyro::admin.settings-manager', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\SettingsManager::class);
        \Livewire\Livewire::component('hyro::admin.plugin-manager', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\PluginManager::class);
        \Livewire\Livewire::component('hyro::admin.plugin-details', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\PluginDetails::class);

        // Register other components
        \Livewire\Livewire::component('hyro::notification-bell', \Marufsharia\Hyro\AdminPanel\Livewire\NotificationBell::class);
        \Livewire\Livewire::component('hyro::notification-center', \Marufsharia\Hyro\AdminPanel\Livewire\NotificationCenter::class);
        \Livewire\Livewire::component('hyro::notification-preferences', \Marufsharia\Hyro\AdminPanel\Livewire\NotificationPreferences::class);
        \Livewire\Livewire::component('hyro::profile-manager', \Marufsharia\Hyro\AdminPanel\Livewire\ProfileManager::class);
    }

    /**
     * Register Blade directives.
     */
    private function registerBladeDirectives(): void
    {
        // Blade directives will be registered here
    }
}

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

        // Auto-publish assets on package installation/update
        $this->autoPublishAssets();

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

        // Built assets from Vite - publish the entire build directory
        $this->publishes([
            __DIR__ . '/../../public/build' => public_path('vendor/hyro'),
        ], 'hyro-assets');

        // Also publish raw CSS/JS for fallback
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/hyro/css'),
            __DIR__ . '/../resources/js' => public_path('vendor/hyro/js'),
        ], 'hyro-assets-raw');

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

    /**
     * Auto-publish assets if they don't exist or are outdated.
     * This ensures assets are available and up-to-date without manual publishing.
     */
    private function autoPublishAssets(): void
    {
        // Only auto-publish during console commands (composer install/update)
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Check if assets need publishing
        if ($this->shouldPublishAssets()) {
            $this->publishAssetsAutomatically();
        }
    }

    /**
     * Determine if assets should be published.
     * 
     * @return bool
     */
    private function shouldPublishAssets(): bool
    {
        $manifestPath = public_path('vendor/hyro/manifest.json');
        
        // If manifest doesn't exist, definitely publish
        if (!\Illuminate\Support\Facades\File::exists($manifestPath)) {
            return true;
        }

        // Check if package manifest is newer than published manifest
        $packageManifest = __DIR__ . '/../../public/build/manifest.json';
        if (!\Illuminate\Support\Facades\File::exists($packageManifest)) {
            return false; // No package manifest, nothing to publish
        }

        // Compare modification times
        $publishedTime = filemtime($manifestPath);
        $packageTime = filemtime($packageManifest);

        // If package manifest is newer, republish
        return $packageTime > $publishedTime;
    }

    /**
     * Publish assets automatically.
     */
    private function publishAssetsAutomatically(): void
    {
        try {
            $buildSource = __DIR__ . '/../../public/build';
            $buildDest = public_path('vendor/hyro');

            if (\Illuminate\Support\Facades\File::exists($buildSource)) {
                // Create destination directory
                if (!\Illuminate\Support\Facades\File::exists($buildDest)) {
                    \Illuminate\Support\Facades\File::makeDirectory($buildDest, 0755, true);
                }

                // Copy built assets
                \Illuminate\Support\Facades\File::copyDirectory($buildSource, $buildDest);

                // Also copy raw assets as fallback
                $cssSource = __DIR__ . '/../resources/css';
                $cssDest = public_path('vendor/hyro/css');
                if (\Illuminate\Support\Facades\File::exists($cssSource)) {
                    \Illuminate\Support\Facades\File::copyDirectory($cssSource, $cssDest);
                }

                $jsSource = __DIR__ . '/../resources/js';
                $jsDest = public_path('vendor/hyro/js');
                if (\Illuminate\Support\Facades\File::exists($jsSource)) {
                    \Illuminate\Support\Facades\File::copyDirectory($jsSource, $jsDest);
                }
            }
        } catch (\Exception $e) {
            // Silently fail - assets can be published manually
        }
    }
}

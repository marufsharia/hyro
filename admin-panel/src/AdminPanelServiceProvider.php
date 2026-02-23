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

        // Register core assets
        $this->registerAssets();
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

        // Built assets from dist directory
        $this->publishes([
            __DIR__ . '/../../dist' => public_path('vendor/hyro'),
        ], 'hyro-assets');

        // Routes
        $this->publishes([
            __DIR__ . '/../routes/admin.php' => base_path('routes/hyro/admin.php'),
            __DIR__ . '/../routes/notifications.php' => base_path('routes/hyro/notifications.php'),
            __DIR__ . '/../routes/profile.php' => base_path('routes/hyro/profile.php'),
        ], 'hyro-routes');
    }

    /**
     * Register core assets using AssetManager.
     */
    private function registerAssets(): void
    {
        $assetHelper = \Marufsharia\Hyro\Core\Support\Assets\AssetHelper::class;
        $assetManager = \Marufsharia\Hyro\Core\Support\Assets\AssetManager::class;

        // Register CSS
        $cssUrl = $assetHelper::getCssUrl();
        if ($cssUrl) {
            $assetManager::registerStyle('hyro-core', $cssUrl);
        } else {
            // Fallback: Register CDN assets
            $this->registerFallbackAssets();
        }

        // Register JS
        $jsUrl = $assetHelper::getJsUrl();
        if ($jsUrl) {
            $assetManager::registerScript('hyro-core', $jsUrl, ['type' => 'module']);
        } else {
            // Fallback: Register CDN scripts
            $this->registerFallbackScripts();
        }

        // Register inline styles for Hyro-specific utilities
        $assetManager::registerInlineStyle('hyro-utilities', $this->getUtilityStyles());
    }

    /**
     * Register fallback CDN assets when built assets are not available.
     */
    private function registerFallbackAssets(): void
    {
        $assetManager = \Marufsharia\Hyro\Core\Support\Assets\AssetManager::class;

        // Register Tailwind CSS from CDN
        $assetManager::registerScript('tailwind-cdn', 'https://cdn.tailwindcss.com');
    }

    /**
     * Register fallback CDN scripts when built assets are not available.
     */
    private function registerFallbackScripts(): void
    {
        $assetManager = \Marufsharia\Hyro\Core\Support\Assets\AssetManager::class;

        // Register Alpine.js and plugins from CDN
        $assetManager::registerScript('alpine-collapse', 'https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js', ['defer' => true]);
        $assetManager::registerScript('alpine-focus', 'https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js', ['defer' => true]);
        $assetManager::registerScript('alpine-intersect', 'https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js', ['defer' => true]);
        $assetManager::registerScript('alpine-core', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', ['defer' => true]);
    }

    /**
     * Get utility styles for Hyro.
     */
    private function getUtilityStyles(): string
    {
        return <<<'CSS'
[x-cloak] { display: none !important; }
.text-balance { text-wrap: balance; }
.glass { 
    background: rgba(255, 255, 255, 0.05); 
    backdrop-filter: blur(10px); 
    border: 1px solid rgba(255, 255, 255, 0.1); 
}
.glass-dark { 
    background: rgba(0, 0, 0, 0.3); 
    backdrop-filter: blur(10px); 
    border: 1px solid rgba(255, 255, 255, 0.1); 
}
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.dark ::-webkit-scrollbar-thumb { background: #475569; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
CSS;
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
        $packageManifest = __DIR__ . '/../../dist/manifest.json';
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
            $distSource = __DIR__ . '/../../dist';
            $distDest = public_path('vendor/hyro');

            if (\Illuminate\Support\Facades\File::exists($distSource)) {
                // Create destination directory
                if (!\Illuminate\Support\Facades\File::exists($distDest)) {
                    \Illuminate\Support\Facades\File::makeDirectory($distDest, 0755, true);
                }

                // Copy built assets from dist
                \Illuminate\Support\Facades\File::copyDirectory($distSource, $distDest);
            }
        } catch (\Exception $e) {
            // Silently fail - assets can be published manually
        }
    }
}

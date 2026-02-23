<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire;

use Livewire\Component;
use Marufsharia\Hyro\Core\Models\HyroSetting;
use Marufsharia\Hyro\Core\Models\Role;
use Marufsharia\Hyro\Services\SettingsService;
use Illuminate\Support\Facades\Artisan;
use Marufsharia\Hyro\Core\Traits\WithAlerts;
class SettingsManager extends Component
{
    use WithAlerts;
    public $activeTab = 'general';
    
    // Available roles for dropdown
    public $availableRoles = [];
    
    // General Settings
    public $locale = 'en';
    public $timezone = 'UTC';
    public $maintenance_mode = false;
    
    // Admin Settings
    public $admin_prefix = 'admin/hyro';
    public $pagination_limit = 15;
    public $layout = 'hyro::admin.layouts.app';
    
    // Appearance Settings
    public $app_name = 'Hyro Admin';
    public $app_logo = '';
    public $app_favicon = '';
    public $primary_color = '#3B82F6';
    public $sidebar_collapsed = false;
    public $theme_mode = 'system';
    
    // CRUD Settings
    public $generate_api = true;
    public $soft_delete = true;
    public $auto_permission = true;
    
    // RBAC Settings
    public $rbac_enabled = true;
    public $default_role = 'user';
    public $super_admin_role = 'super-admin';
    public $cache_permissions = true;
    
    // Plugin Settings
    public $auto_load = true;
    public $allow_disable = true;
    
    // System Settings
    public $cache_enabled = true;
    public $cache_ttl = 3600;
    public $auditing_enabled = true;

    protected $queryString = ['activeTab'];

    public function mount()
    {
        // Check permission
        if (!auth()->user()->hasPrivilege('settings.manage')) {
            abort(403, 'Unauthorized access to settings.');
        }

        // Load available roles for dropdown
        $this->availableRoles = Role::orderBy('name')->get(['slug', 'name'])->toArray();

        $this->loadSettings();
    }

    public function loadSettings()
    {
        // General
        $this->locale = hyro_config('locale', 'en');
        $this->timezone = hyro_config('timezone', 'UTC');
        $this->maintenance_mode = hyro_config('maintenance_mode', false);
        
        // Admin
        $this->admin_prefix = hyro_config('admin.route.prefix', 'admin/hyro');
        $this->pagination_limit = hyro_config('admin.pagination.per_page', 15);
        $this->layout = hyro_config('admin.layout', 'hyro::admin.layouts.app');
        
        // Appearance
        $this->app_name = hyro_config('appearance.app_name', config('app.name', 'Hyro Admin'));
        $this->app_logo = hyro_config('appearance.app_logo', '');
        $this->app_favicon = hyro_config('appearance.app_favicon', '');
        $this->primary_color = hyro_config('appearance.primary_color', '#3B82F6');
        $this->sidebar_collapsed = hyro_config('appearance.sidebar_collapsed', false);
        $this->theme_mode = hyro_config('appearance.theme_mode', 'system');
        
        // CRUD
        $this->generate_api = hyro_config('crud.generate_api', true);
        $this->soft_delete = hyro_config('crud.soft_delete', true);
        $this->auto_permission = hyro_config('crud.auto_permission', true);
        
        // RBAC
        $this->rbac_enabled = hyro_config('rbac.enabled', true);
        $this->default_role = hyro_config('rbac.default_role', 'user');
        $this->super_admin_role = hyro_config('rbac.super_admin_role', 'super-admin');
        $this->cache_permissions = hyro_config('rbac.cache_permissions', true);
        
        // Plugins
        $this->auto_load = hyro_config('plugins.autoload', true);
        $this->allow_disable = hyro_config('plugins.allow_disable', true);
        
        // System
        $this->cache_enabled = hyro_config('cache.enabled', true);
        $this->cache_ttl = hyro_config('cache.ttl.roles', 3600);
        $this->auditing_enabled = hyro_config('auditing.enabled', true);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral()
    {
        $this->validate([
            'locale' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
        ]);

        hyro_set_config('locale', $this->locale);
        hyro_set_config('timezone', $this->timezone);
        hyro_set_config('maintenance_mode', $this->maintenance_mode);

        $this->toastSuccess('General settings saved successfully.');
    }

    public function saveAdmin()
    {
        $this->validate([
            'admin_prefix' => 'required|string|max:50',
            'pagination_limit' => 'required|integer|min:5|max:100',
        ]);

        hyro_set_config('admin.route.prefix', $this->admin_prefix);
        hyro_set_config('admin.pagination.per_page', $this->pagination_limit);
        hyro_set_config('admin.layout', $this->layout);

        $this->toastSuccess('Admin settings saved. Please clear route cache.');
    }

    public function saveAppearance()
    {
        try {
            $this->validate([
                'app_name' => 'required|string|max:100',
                'primary_color' => 'required|string|max:20',
                'theme_mode' => 'required|in:light,dark,system',
            ]);

            hyro_set_config('appearance.app_name', $this->app_name);
            hyro_set_config('appearance.app_logo', $this->app_logo);
            hyro_set_config('appearance.app_favicon', $this->app_favicon);
            hyro_set_config('appearance.primary_color', $this->primary_color);
            hyro_set_config('appearance.sidebar_collapsed', $this->sidebar_collapsed);
            hyro_set_config('appearance.theme_mode', $this->theme_mode);

            // Show success message
            $this->toastSuccess('Appearance settings saved successfully.');
            
            // Dispatch event to refresh all components
            $this->dispatch('appearance-updated');
            
            // Refresh the page to apply changes after showing toast
            $this->dispatch('refresh-page');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so Livewire can handle them
            throw $e;
        } catch (\Exception $e) {
            // Show error message
            $this->toastError('Failed to save appearance settings: ' . $e->getMessage());
            \Log::error('Appearance save failed: ' . $e->getMessage());
        }
    }

    /**
     * Update theme mode without showing alert
     */
    public function updateThemeMode($mode)
    {
        try {
            // Validate mode
            if (!in_array($mode, ['light', 'dark', 'system'])) {
                return;
            }
            
            $this->theme_mode = $mode;
            hyro_set_config('appearance.theme_mode', $mode);
            
            // Dispatch simple event without parameters to avoid serialization issues
            $this->dispatch('theme-mode-updated');
        } catch (\Exception $e) {
            // Silent fail - no alert
            \Log::error('Theme mode update failed: ' . $e->getMessage());
        }
    }

    public function saveCrud()
    {
        hyro_set_config('crud.generate_api', $this->generate_api);
        hyro_set_config('crud.soft_delete', $this->soft_delete);
        hyro_set_config('crud.auto_permission', $this->auto_permission);

        $this->toastSuccess('CRUD settings saved successfully.');
    }

    public function saveRbac()
    {
        $this->validate([
            'default_role' => 'required|string|max:50',
            'super_admin_role' => 'required|string|max:50',
        ]);

        hyro_set_config('rbac.enabled', $this->rbac_enabled);
        hyro_set_config('rbac.default_role', $this->default_role);
        hyro_set_config('rbac.super_admin_role', $this->super_admin_role);
        hyro_set_config('rbac.cache_permissions', $this->cache_permissions);

        $this->toastSuccess('RBAC settings saved successfully.');
    }

    public function savePlugins()
    {
        hyro_set_config('plugins.autoload', $this->auto_load);
        hyro_set_config('plugins.allow_disable', $this->allow_disable);

        $this->toastSuccess('Plugin settings saved successfully.');
    }

    public function saveSystem()
    {
        $this->validate([
            'cache_ttl' => 'required|integer|min:60|max:86400',
        ]);

        hyro_set_config('cache.enabled', $this->cache_enabled);
        hyro_set_config('cache.ttl.roles', $this->cache_ttl);
        hyro_set_config('auditing.enabled', $this->auditing_enabled);

        $this->toastSuccess('System settings saved successfully.');
    }

    public function clearConfigCache()
    {
        Artisan::call('config:clear');
        $this->toastSuccess('Config cache cleared successfully.');
    }

    public function clearRouteCache()
    {
        Artisan::call('route:clear');
        $this->toastSuccess('Route cache cleared successfully.');
    }

    public function clearAppCache()
    {
        Artisan::call('cache:clear');
        $this->toastSuccess('Application cache cleared successfully.');
    }

    public function clearAllCaches()
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('cache:clear');
        HyroSetting::clearCache();
        
        $this->toastSuccess('All caches cleared successfully.');
    }

    public function render()
    {
        return view('hyro::admin.settings.manager')
            ->layout('hyro::admin.layouts.app');
    }
}

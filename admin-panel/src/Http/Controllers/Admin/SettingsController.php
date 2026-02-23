<?php

namespace Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marufsharia\Hyro\Services\SettingsService;

class SettingsController extends Controller
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        
        // Apply permission middleware
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            if (!$user || !$user->hasPrivilege('settings.manage')) {
                abort(403, 'Unauthorized access to settings.');
            }
            return $next($request);
        });
    }

    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = $this->settingsService->getAllSettings();
        
        return view('hyro::admin.settings.index', compact('settings'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'maintenance_mode' => 'boolean',
        ]);

        $this->settingsService->updateSettings('general', $validated);

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Update admin settings.
     */
    public function updateAdmin(Request $request)
    {
        $validated = $request->validate([
            'admin_prefix' => 'required|string|max:50',
            'pagination_limit' => 'required|integer|min:5|max:100',
            'layout' => 'required|string|max:255',
        ]);

        $this->settingsService->updateSettings('admin', $validated);

        return back()->with('success', 'Admin settings updated successfully. Please clear route cache.');
    }

    /**
     * Update CRUD settings.
     */
    public function updateCrud(Request $request)
    {
        $validated = $request->validate([
            'generate_api' => 'boolean',
            'soft_delete' => 'boolean',
            'auto_permission' => 'boolean',
        ]);

        $this->settingsService->updateSettings('crud', $validated);

        return back()->with('success', 'CRUD settings updated successfully.');
    }

    /**
     * Update RBAC settings.
     */
    public function updateRbac(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'default_role' => 'required|string|max:50',
            'super_admin_role' => 'required|string|max:50',
            'cache_permissions' => 'boolean',
        ]);

        $this->settingsService->updateSettings('rbac', $validated);

        return back()->with('success', 'RBAC settings updated successfully.');
    }

    /**
     * Update plugin settings.
     */
    public function updatePlugins(Request $request)
    {
        $validated = $request->validate([
            'auto_load' => 'boolean',
            'allow_disable' => 'boolean',
        ]);

        $this->settingsService->updateSettings('plugins', $validated);

        return back()->with('success', 'Plugin settings updated successfully.');
    }

    /**
     * Update system settings.
     */
    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'cache_enabled' => 'boolean',
            'cache_ttl' => 'required|integer|min:60|max:86400',
            'auditing_enabled' => 'boolean',
        ]);

        $this->settingsService->updateSettings('system', $validated);

        return back()->with('success', 'System settings updated successfully.');
    }

    /**
     * Clear config cache.
     */
    public function clearConfigCache()
    {
        $this->settingsService->clearConfigCache();
        
        return back()->with('success', 'Config cache cleared successfully.');
    }

    /**
     * Clear route cache.
     */
    public function clearRouteCache()
    {
        $this->settingsService->clearRouteCache();
        
        return back()->with('success', 'Route cache cleared successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearAppCache()
    {
        $this->settingsService->clearAppCache();
        
        return back()->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Clear all caches.
     */
    public function clearAllCaches()
    {
        $this->settingsService->clearAllCaches();
        
        return back()->with('success', 'All caches cleared successfully.');
    }
}

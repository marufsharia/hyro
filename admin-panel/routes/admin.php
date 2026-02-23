<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\AdminPanel\Http\Controllers\Admin\UserRoleController;
use Marufsharia\Hyro\AdminPanel\Livewire\Admin\Dashboard;
use Marufsharia\Hyro\AdminPanel\Livewire\Admin\RoleManager;
use Marufsharia\Hyro\AdminPanel\Livewire\Admin\PrivilegeManager;

/*
|--------------------------------------------------------------------------
| Hyro Admin Panel Routes
|--------------------------------------------------------------------------
|
| All admin routes are prefixed with 'admin/hyro' by default and
| protected by 'web' and 'auth' middleware.
|
*/

//if (!config('hyro.admin.enabled')) {
//    return;
//}

Route::prefix(hyro_config('admin.route.prefix', config('hyro.admin.route.prefix')))
    ->middleware(array_merge(config('hyro.admin.route.middleware'), ['hyro.2fa']))
    ->name('hyro.admin.')
    ->group(function () {
        // Dashboard - Livewire Component
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        // Roles - Livewire Component
        Route::get('/roles', RoleManager::class)->name('roles.index');

        // Privileges - Livewire Component
        Route::get('/privileges', PrivilegeManager::class)->name('privileges.index');

        // User → Role Management (keeping controller for now)
        Route::prefix('users/{user}')->name('users.')->group(function () {
            Route::get('roles', [UserRoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles', [UserRoleController::class, 'update'])->name('roles.update');
        });

        // Plugin Manager - Livewire Components
        Route::get('/plugins', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\PluginManager::class)->name('plugins.index');
        Route::get('/plugins/{pluginId}', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\PluginDetails::class)->name('plugins.show');

        // Settings
        Route::get('/settings', \Marufsharia\Hyro\AdminPanel\Livewire\Admin\SettingsManager::class)->name('settings.index');
    });


<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Admin\UserRoleController;
use Marufsharia\Hyro\Livewire\Admin\Dashboard;
use Marufsharia\Hyro\Livewire\Admin\RoleManager;
use Marufsharia\Hyro\Livewire\Admin\PrivilegeManager;
require __DIR__.'/auth.php';
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

Route::prefix(config('hyro.admin.route.prefix'))
    ->middleware(config('hyro.admin.route.middleware'))
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

        // Plugin Manager
        Route::get('/plugins', [\Marufsharia\Hyro\Http\Controllers\Admin\PluginController::class, 'index'])->name('plugins');
    });


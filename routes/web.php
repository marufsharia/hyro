<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Admin\DashboardController;
use Marufsharia\Hyro\Http\Controllers\Admin\RoleController;
use Marufsharia\Hyro\Http\Controllers\Admin\PrivilegeController;
use Marufsharia\Hyro\Http\Controllers\Admin\UserRoleController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Hyro Admin Panel Routes
|--------------------------------------------------------------------------
|
| All admin routes are prefixed with 'admin/hyro' by default and
| protected by 'web' and 'auth' middleware.
|
*/
Route::prefix(config('hyro.ui.route_prefix', 'admin/hyro'))
    ->middleware(config('hyro.ui.middleware', ['web', 'auth']))
    ->name('hyro.admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Roles
        Route::resource('roles', RoleController::class)->except(['show']);

        // Privileges
        Route::resource('privileges', PrivilegeController::class)->except(['show']);

        // User → Role Management
        Route::prefix('users/{user}')->name('users.')->group(function () {
            Route::get('roles', [UserRoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles', [UserRoleController::class, 'update'])->name('roles.update');
        });

        // Role → Privilege Management
        Route::prefix('roles/{role}')->name('roles.')->group(function () {
            Route::get('privileges', [RoleController::class, 'editPrivileges'])->name('privileges.edit');
            Route::put('privileges', [RoleController::class, 'updatePrivileges'])->name('privileges.update');
        });
    });

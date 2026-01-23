<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Admin\DashboardController;
use Marufsharia\Hyro\Http\Controllers\Admin\RoleController;
use Marufsharia\Hyro\Http\Controllers\Admin\PrivilegeController;
use Marufsharia\Hyro\Http\Controllers\Admin\UserRoleController;
use Marufsharia\Hyro\Http\Middleware\EnsureHasPrivilege;

/*
 |--------------------------------------------------------------------------
 | Authentication Routes
 |--------------------------------------------------------------------------
 */
require __DIR__ . '/auth.php';


Route::group([
    'prefix'     => config('hyro.ui.route_prefix', 'admin/hyro'),
    'middleware' => config('hyro.ui.middleware', ['web', 'auth']),
], function () {

    /*
     |--------------------------------------------------------------------------
     | Dashboard
     |--------------------------------------------------------------------------
     */
    Route::get('/', [DashboardController::class, 'index'])
        ->name('hyro.admin.dashboard');

    /*
     |--------------------------------------------------------------------------
     | Roles
     |--------------------------------------------------------------------------
     */
    Route::resource('roles', RoleController::class)
        ->except('show');

    /*
     |--------------------------------------------------------------------------
     | Privileges
     |--------------------------------------------------------------------------
     */
    Route::resource('privileges', PrivilegeController::class)
        ->except('show');

    /*
     |--------------------------------------------------------------------------
     | User → Role Management
     |--------------------------------------------------------------------------
     */
    Route::prefix('users/{user}')
        ->name('users.')
        ->group(function () {

            Route::get('roles', [UserRoleController::class, 'edit'])
                ->name('roles.edit');

            Route::put('roles', [UserRoleController::class, 'update'])
                ->name('roles.update');
        });

    /*
     |--------------------------------------------------------------------------
     | Role → Privilege Management
     |--------------------------------------------------------------------------
     */
    Route::prefix('roles/{role}')
        ->name('roles.')
        ->group(function () {

            Route::get('privileges', [RoleController::class, 'editPrivileges'])
                ->name('privileges.edit');

            Route::put('privileges', [RoleController::class, 'updatePrivileges'])
                ->name('privileges.update');
        });
});


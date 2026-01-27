<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Api\AuthController;
use Marufsharia\Hyro\Http\Controllers\Api\PrivilegeController;
use Marufsharia\Hyro\Http\Controllers\Api\RoleController;
use Marufsharia\Hyro\Http\Controllers\Api\SuspensionController;
use Marufsharia\Hyro\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are protected by Sanctum authentication and require
| appropriate privileges as defined in the controllers.
|
*/

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::get('check-privilege/{privilege}', [AuthController::class, 'checkPrivilege']);
        Route::get('check-role/{role}', [AuthController::class, 'checkRole']);

        // Token management
        Route::prefix('tokens')->group(function () {
            Route::get('/', [AuthController::class, 'tokens']);
            Route::post('/', [AuthController::class, 'createToken']);
            Route::delete('{token}', [AuthController::class, 'revokeToken']);
            Route::delete('/', [AuthController::class, 'revokeAllTokens']);
        });
    });
});

// User management
Route::apiResource('users', UserController::class)->except(['store']);
Route::prefix('users/{users}')->group(function () {
    // Roles
    Route::post('roles', [UserController::class, 'assignRole']);
    Route::delete('roles/{role}', [UserController::class, 'removeRole']);
    Route::get('roles', [UserController::class, 'roles']);

    // Privileges
    Route::get('privileges', [UserController::class, 'privileges']);

    // Suspensions
    Route::post('suspend', [UserController::class, 'suspend']);
    Route::post('unsuspend', [UserController::class, 'unsuspend']);
    Route::get('suspensions', [UserController::class, 'suspensions']);
    Route::get('active-suspension', [UserController::class, 'activeSuspension']);

    // Tokens
    Route::post('sync-tokens', [UserController::class, 'syncTokens']);
    Route::delete('tokens', [UserController::class, 'revokeAllTokens']);
});

// Role management
Route::apiResource('roles', RoleController::class);
Route::prefix('roles/{role}')->group(function () {
    // Privileges
    Route::post('privileges', [RoleController::class, 'grantPrivilege']);
    Route::delete('privileges/{privilege}', [RoleController::class, 'revokePrivilege']);
    Route::get('privileges', [RoleController::class, 'privileges']);
    Route::get('has-privilege/{privilege}', [RoleController::class, 'hasPrivilege']);

    // Users
    Route::get('users', [RoleController::class, 'users']);
});

// Privilege management
Route::apiResource('privileges', PrivilegeController::class);
Route::prefix('privileges')->group(function () {
    // Categories
    Route::get('categories', [PrivilegeController::class, 'categories']);

    // Search
    Route::get('search', [PrivilegeController::class, 'search']);
});
Route::prefix('privileges/{privilege}')->group(function () {
    // Expand wildcard
    Route::get('expand', [PrivilegeController::class, 'expand']);

    // Match check
    Route::get('matches/{slug}', [PrivilegeController::class, 'matches']);

    // Roles
    Route::get('roles', [PrivilegeController::class, 'roles']);
});

// Suspension management
Route::apiResource('suspensions', SuspensionController::class)->except(['destroy']);
Route::prefix('suspensions')->group(function () {
    // Active suspensions
    Route::get('active', [SuspensionController::class, 'active']);

    // Expired suspensions
    Route::get('expired', [SuspensionController::class, 'expired']);

    // Statistics
    Route::get('statistics', [SuspensionController::class, 'statistics']);
});

// Health check endpoint (public)
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'hyro-api',
        'version' => config('hyro.api.version', '1.0'),
    ]);
});

// API documentation endpoint
Route::get('documentation', function () {
    return response()->json([
        'name' => 'Hyro API',
        'version' => config('hyro.api.version', '1.0'),
        'description' => 'Enterprise-grade authorization system API',
        'endpoints' => [
            'auth' => [
                'POST /api/hyro/auth/login' => 'Authenticate users',
                'POST /api/hyro/auth/register' => 'Register new users',
                'POST /api/hyro/auth/logout' => 'Logout users',
                'GET /api/hyro/auth/me' => 'Get authenticated users',
                'GET /api/hyro/auth/check-privilege/{privilege}' => 'Check if users has privilege',
                'GET /api/hyro/auth/check-role/{role}' => 'Check if users has role',
            ],
            'users' => [
                'GET /api/hyro/users' => 'List all users',
                'GET /api/hyro/users/{id}' => 'Get users details',
                'PUT /api/hyro/users/{id}' => 'Update users',
                'DELETE /api/hyro/users/{id}' => 'Delete users',
            ],
            'roles' => [
                'GET /api/hyro/roles' => 'List all roles',
                'POST /api/hyro/roles' => 'Create role',
                'GET /api/hyro/roles/{id}' => 'Get role details',
                'PUT /api/hyro/roles/{id}' => 'Update role',
                'DELETE /api/hyro/roles/{id}' => 'Delete role',
            ],
            'privileges' => [
                'GET /api/hyro/privileges' => 'List all privileges',
                'POST /api/hyro/privileges' => 'Create privilege',
                'GET /api/hyro/privileges/{id}' => 'Get privilege details',
                'PUT /api/hyro/privileges/{id}' => 'Update privilege',
                'DELETE /api/hyro/privileges/{id}' => 'Delete privilege',
            ],
        ],
        'authentication' => 'Bearer token (Sanctum)',
        'rate_limiting' => config('hyro.api.rate_limit.max_attempts', 60) . ' requests per minute',
    ]);
});

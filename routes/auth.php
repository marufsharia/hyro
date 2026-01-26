<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\AuthController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\RegisterController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\ForgotPasswordController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\ResetPasswordController;

Route::prefix(config('hyro.admin.route.prefix'))
    ->middleware('web')
    ->name('hyro.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Guest Routes (login, register, password reset)
        |--------------------------------------------------------------------------
        */
        Route::middleware('hyro.guest')->group(function () {

            // Login page or redirect to dashboard if already logged in
            Route::get('/', function () {
                if (auth()->check()) {
                    return redirect()->route('hyro.admin.dashboard');
                }
                return app(AuthController::class)->showLoginForm();
            })->name('login');

            // Login submission
            Route::post('login', [AuthController::class, 'login'])
                ->name('login.submit');

            // Registration page
            Route::get('register', [RegisterController::class, 'showRegistrationForm'])
                ->name('register');

            // Registration submission
            Route::post('register', [RegisterController::class, 'register'])
                ->name('register.submit');

            // Password reset request form
            Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
                ->name('password.request');

            // Send password reset link
            Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
                ->name('password.email');

            // Password reset form (with token)
            Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
                ->name('password.reset');

            // Password reset submission
            Route::post('password/reset', [ResetPasswordController::class, 'reset'])
                ->name('password.update');
        });

        /*
        |--------------------------------------------------------------------------
        | Authenticated Routes (logout)
        |--------------------------------------------------------------------------
        */
        Route::middleware('auth')->group(function () {

            // Logout
            Route::post('logout', [AuthController::class, 'logout'])
                ->name('logout');
        });

    });

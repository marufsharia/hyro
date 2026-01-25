<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\AuthController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\ForgotPasswordController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\RegisterController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\ResetPasswordController;

Route::prefix(config('hyro.ui.route_prefix', 'admin/hyro'))
    ->middleware('web')
    ->name('hyro.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        /*
        |--------------------------------------------------------------------------
        | Registration
        |--------------------------------------------------------------------------
        */
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);

        /*
        |--------------------------------------------------------------------------
        | Password Reset
        |--------------------------------------------------------------------------
        */
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
            ->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])
            ->name('password.update');
    });

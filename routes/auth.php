<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\AuthController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\ForgotPasswordController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\RegisterController;
use Marufsharia\Hyro\Http\Controllers\Admin\Auth\ResetPasswordController;

Route::middleware('web')->group(function () {

    /*
     |--------------------------------------------------------------------------
     | Authentication
     |--------------------------------------------------------------------------
     */
    Route::get('login', [AuthController::class, 'showLoginForm'])
        ->name('hyro.login');

    Route::post('login', [AuthController::class, 'login']);

    Route::post('logout', [AuthController::class, 'logout'])
        ->name('hyro.logout');

    /*
     |--------------------------------------------------------------------------
     | Registration
     |--------------------------------------------------------------------------
     */
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
        ->name('hyro.register');

    Route::post('register', [RegisterController::class, 'register']);

    /*
     |--------------------------------------------------------------------------
     | Password Reset
     |--------------------------------------------------------------------------
     */
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('hyro.password.request');

    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('hyro.password.email');

    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('hyro.password.reset');

    Route::post('password/reset', [ResetPasswordController::class, 'reset'])
        ->name('hyro.password.update');
});

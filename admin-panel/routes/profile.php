<?php

use Marufsharia\Hyro\AdminPanel\Livewire\ProfileManager;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
|
| Here are the routes for user profile management. These routes require
| authentication and allow users to manage their profile, security settings,
| two-factor authentication, and account deletion.
|
*/

// Create a login route alias if it doesn't exist
if (!Route::has('login')) {
    Route::get('/login', function () {
        return redirect()->route('hyro.login');
    })->name('login');
}

Route::middleware(['web', 'auth'])->prefix('profile')->name('profile.')->group(function () {
    
    // Main Profile Management Page
    Route::get('/', ProfileManager::class)->name('index');
    
    // Alternative route names for convenience
    Route::get('/settings', ProfileManager::class)->name('settings');
    Route::get('/manage', ProfileManager::class)->name('manage');
    
});

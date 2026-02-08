<?php

use Illuminate\Support\Facades\Route;
use Marufsharia\Hyro\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
|
| Routes for managing user notifications
|
*/

Route::middleware(['web', 'auth'])->prefix('notifications')->name('notifications.')->group(function () {
    // Notification center
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    
    // Mark as read
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    
    // Delete
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    
    // Preferences
    Route::get('/preferences', [NotificationController::class, 'preferences'])->name('preferences');
    Route::post('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
});

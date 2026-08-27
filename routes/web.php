<?php

use App\Enums\Permission;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('welcome');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', NotificationController::class)->middleware('throttle:notifications')->name('notifications.index');

    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_USERS->value);
        Route::get('/{user}', 'show')->name('show')->middleware('can:'.Permission::VIEW_USERS->value);
        if (config('app.feature.user_ban')) {
            Route::patch('/{user}/toggle', 'toggle')->name('toggle')->middleware(['can:'.Permission::TOGGLE_USERS->value, 'throttle:user-actions']);
        }
    });
});

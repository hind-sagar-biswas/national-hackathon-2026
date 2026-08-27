<?php

use App\Http\Controllers\Api\V1\HealthzController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user())->name('user');

    Route::prefix('notifications')
        ->middleware('throttle:notifications')
        ->name('notifications.')
        ->controller(NotificationController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/read', 'markAsRead')->name('read');
            Route::post('/read-all', 'markAllAsRead')->name('read-all');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });
});

Route::get('/healthz', HealthzController::class)->name('site-health');

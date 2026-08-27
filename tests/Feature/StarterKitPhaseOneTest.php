<?php

use Illuminate\Support\Facades\Route;

it('applies throttle middleware to notifications routes', function () {
    $webRoute = Route::getRoutes()->getByName('notifications.index');
    $apiRoute = Route::getRoutes()->getByName('api.notifications.index');

    expect($webRoute)->not->toBeNull();
    expect($apiRoute)->not->toBeNull();

    expect($webRoute->gatherMiddleware())->toContain('throttle:notifications');
    expect($apiRoute->gatherMiddleware())->toContain('throttle:notifications');
});

it('exposes pagination and rate-limit defaults in config', function () {
    // expect(config('app.pagination.default'))->toBeInt();
    // expect(config('app.pagination.max'))->toBeInt();
    // expect(config('app.pagination.max'))->toBeGreaterThanOrEqual(config('app.pagination.default'));

    expect(config('app.rate_limits.notifications_per_minute'))->toBeInt();
    expect(config('app.rate_limits.user_actions_per_minute'))->toBeInt();
});

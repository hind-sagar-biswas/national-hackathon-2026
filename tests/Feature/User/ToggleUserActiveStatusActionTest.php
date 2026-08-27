<?php

use App\Actions\User\ToggleUserActiveStatusAction;
use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use App\Notifications\AccountDeactivatedNotification;
use Illuminate\Support\Facades\Notification;

it('toggles an inactive user to active and sends an activation notification', function () {
    Notification::fake();

    $user = User::factory()->create(['is_active' => false]);

    $isActive = app(ToggleUserActiveStatusAction::class)->handle($user);

    expect($isActive)->toBeTrue();
    expect($user->fresh()->is_active)->toBeTrue();

    Notification::assertSentTo($user, AccountActivatedNotification::class);
});

it('toggles an active user to inactive and sends a deactivation notification', function () {
    Notification::fake();

    $user = User::factory()->create(['is_active' => true]);

    $isActive = app(ToggleUserActiveStatusAction::class)->handle($user);

    expect($isActive)->toBeFalse();
    expect($user->fresh()->is_active)->toBeFalse();

    Notification::assertSentTo($user, AccountDeactivatedNotification::class);
});

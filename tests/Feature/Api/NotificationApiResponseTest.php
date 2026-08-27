<?php

use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

it('returns a standardized envelope for the notifications api index endpoint', function () {
    $user = User::factory()->create();
    Notification::sendNow($user, new AccountActivatedNotification);

    $response = actingAs($user)
        ->getJson(route('api.notifications.index'));

    $response
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'notifications',
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'has_more'],
                'unread_count',
            ],
        ]);
});

it('returns a standardized envelope when marking a notification as read', function () {
    $user = User::factory()->create();
    Notification::sendNow($user, new AccountActivatedNotification);

    $notificationId = $user->fresh()->notifications()->firstOrFail()->id;

    $response = actingAs($user)
        ->postJson(route('api.notifications.read', ['id' => $notificationId]));

    $response
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['unread_count'],
        ]);
});

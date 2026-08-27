<?php

namespace App\Actions\User;

use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use App\Notifications\AccountDeactivatedNotification;

class ToggleUserActiveStatusAction
{
    public function handle(User $user): bool
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        if ($user->is_active) {
            $user->notify(new AccountActivatedNotification);
        } else {
            $user->notify(new AccountDeactivatedNotification);
        }

        return $user->is_active;
    }
}

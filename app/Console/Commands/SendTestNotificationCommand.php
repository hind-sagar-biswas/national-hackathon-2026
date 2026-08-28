<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notify:test {user=1}')]
#[Description('Sends a test AccountActivatedNotification to the specified user.')]
class SendTestNotificationCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user');
        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return Command::FAILURE;
        }

        $user->notifyNow(new AccountActivatedNotification);

        $this->info("Successfully sent AccountActivatedNotification to user {$user->name} ({$user->email}).");

        return Command::SUCCESS;
    }
}

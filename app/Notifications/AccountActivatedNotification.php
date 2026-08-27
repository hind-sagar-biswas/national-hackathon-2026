<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class AccountActivatedNotification extends Notification
{
    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'check-circle';
    }

    protected function getActionUrl(): ?string
    {
        return route('login');
    }

    protected function getActionText(): string
    {
        return 'Login';
    }

    protected function getNotificationTitle(): string
    {
        return 'Your Account Has Been Activated';
    }

    protected function getNotificationMessage(): string
    {
        return 'Your account has been reactivated. Welcome back!';
    }

    protected function getNotificationData(): array
    {
        return [
            'activated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Your Account Has Been Reactivated';

        $message = $this->buildMailMessage($subject, $notifiable);
        $message->line('Great news! Your deactivated account has been reactivated.');

        return $this->addActionButton($message)
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

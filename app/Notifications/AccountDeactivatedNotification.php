<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class AccountDeactivatedNotification extends Notification
{
    protected function getNotificationType(): string
    {
        return 'error';
    }

    protected function getNotificationIcon(): string
    {
        return 'times-circle';
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
        return 'Your Account Has Been Deactivated';
    }

    protected function getNotificationMessage(): string
    {
        return 'Your account has been deactivated!';
    }

    protected function getNotificationData(): array
    {
        return [
            'deactivated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Your Account Has Been Deactivated';

        $message = $this->buildMailMessage($subject, $notifiable);
        $message->line('We regret to inform you that your account has been deactivated.')
            ->line('If you have any questions or concerns, please contact our support team.');

        return $this->addActionButton($message)
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

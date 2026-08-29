<?php

namespace App\Notifications\Banking;

use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SuspiciousActivityAlertNotification extends Notification
{
    public function __construct(
        public string $actionDescription,
        public ?string $ip = null,
    ) {}

    protected function getNotificationType(): string
    {
        return 'warning';
    }

    protected function getNotificationIcon(): string
    {
        return 'shield-alert';
    }

    protected function getActionUrl(): ?string
    {
        return route('profile.show');
    }

    protected function getActionText(): string
    {
        return 'Review Account Security';
    }

    protected function getNotificationTitle(): string
    {
        return 'Security Notice: Unusual Activity';
    }

    protected function getNotificationMessage(): string
    {
        $ipText = $this->ip ? " (IP: {$this->ip})" : '';

        return "We detected unusual activity regarding: {$this->actionDescription}{$ipText}. If this was you, you can ignore this message. If not, please change your password and enable Two-Factor Authentication.";
    }

    protected function getNotificationData(): array
    {
        return [
            'action_description' => $this->actionDescription,
            'ip' => $this->ip,
            'detected_at' => now()->toIso8601String(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Security Alert: Unusual Activity Detected', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Please review your active browser sessions in account settings.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

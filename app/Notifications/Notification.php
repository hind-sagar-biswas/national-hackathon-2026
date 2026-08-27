<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

abstract class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    abstract public function toMail(object $notifiable): MailMessage;

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(object $notifiable): array;

    /**
     * Get the notification type for styling.
     */
    protected function getNotificationType(): string
    {
        return 'info';
    }

    /**
     * Get the notification icon.
     */
    protected function getNotificationIcon(): string
    {
        return 'bell';
    }

    /**
     * Get the action URL for the notification.
     */
    protected function getActionUrl(): ?string
    {
        return null;
    }

    /**
     * Get the action text for the notification.
     */
    protected function getActionText(): string
    {
        return 'View';
    }

    /**
     * Get the notification title.
     */
    abstract protected function getNotificationTitle(): string;

    /**
     * Get the notification message.
     */
    abstract protected function getNotificationMessage(): string;

    /**
     * Get additional data for the notification.
     */
    protected function getNotificationData(): array
    {
        return [];
    }

    /**
     * Build the base array representation.
     */
    protected function buildNotificationArray(): array
    {
        return [
            'title' => $this->getNotificationTitle(),
            'message' => $this->getNotificationMessage(),
            'type' => $this->getNotificationType(),
            'icon' => $this->getNotificationIcon(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'data' => $this->getNotificationData(),
        ];
    }

    /**
     * Build the base mail message.
     */
    protected function buildMailMessage(string $subject, object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hi '.$this->getNotifiableName($notifiable).',')
            ->line($this->getNotificationMessage());
    }

    /**
     * Get the notifiable's name.
     */
    protected function getNotifiableName(object $notifiable): string
    {
        return $notifiable->name ?? 'User';
    }

    /**
     * Add action button to mail if action URL is available.
     */
    protected function addActionButton(MailMessage $message): MailMessage
    {
        if ($this->getActionUrl()) {
            $message->action($this->getActionText(), $this->getActionUrl());
        }

        return $message;
    }
}

<?php

namespace App\Notifications\Banking;

use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RegistrationBonusReceivedNotification extends Notification
{
    public function __construct(
        public int $amount = 100000,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'gift';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'Go to Dashboard';
    }

    protected function getNotificationTitle(): string
    {
        return 'Registration Bonus Credited!';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = formatPaisa($this->amount);

        return "Welcome to MaMoney! Your wallet has been credited with a registration bonus of {$formatted} {$this->currency}.";
    }

    protected function getNotificationData(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Welcome to MaMoney - Bonus Credited!', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('You can start making transfers, requesting funds, or exploring peer-to-peer loans immediately.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

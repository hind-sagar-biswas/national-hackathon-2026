<?php

namespace App\Notifications\Banking;

use App\Models\DepositRequest;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DepositConfirmedNotification extends Notification
{
    public function __construct(
        public DepositRequest $depositRequest,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'wallet';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Wallet';
    }

    protected function getNotificationTitle(): string
    {
        return 'Deposit Confirmed';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = formatPaisa($this->depositRequest->amount);
        $providerName = ucfirst($this->depositRequest->provider->value);

        return "Your deposit of {$formatted} {$this->currency} via {$providerName} has been verified and added to your wallet.";
    }

    protected function getNotificationData(): array
    {
        return [
            'deposit_request_id' => $this->depositRequest->id,
            'provider' => $this->depositRequest->provider->value,
            'provider_ref' => $this->depositRequest->provider_ref,
            'amount' => $this->depositRequest->amount,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Deposit Confirmed - MaMoney', $notifiable)
            ->line($this->getNotificationMessage())
            ->line("Provider Reference: {$this->depositRequest->provider_ref}");

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

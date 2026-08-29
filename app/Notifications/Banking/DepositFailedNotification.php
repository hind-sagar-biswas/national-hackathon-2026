<?php

namespace App\Notifications\Banking;

use App\Models\DepositRequest;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DepositFailedNotification extends Notification
{
    public function __construct(
        public DepositRequest $depositRequest,
        public ?string $reason = null,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'error';
    }

    protected function getNotificationIcon(): string
    {
        return 'alert-circle';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Details';
    }

    protected function getNotificationTitle(): string
    {
        return 'Deposit Failed';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = number_format($this->depositRequest->amount);
        $providerName = ucfirst($this->depositRequest->provider->value);
        $reasonText = $this->reason ? " Reason: {$this->reason}." : '';

        return "Your deposit of {$formatted} {$this->currency} via {$providerName} could not be confirmed.{$reasonText}";
    }

    protected function getNotificationData(): array
    {
        return [
            'deposit_request_id' => $this->depositRequest->id,
            'provider' => $this->depositRequest->provider->value,
            'provider_ref' => $this->depositRequest->provider_ref,
            'amount' => $this->depositRequest->amount,
            'reason' => $this->reason,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Deposit Failed - MaMoney', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Please check your transaction reference and retry, or reach out to support.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

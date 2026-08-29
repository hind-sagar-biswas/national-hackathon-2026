<?php

namespace App\Notifications\Banking;

use App\Models\MoneyRequest;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MoneyRequestReceivedNotification extends Notification
{
    public function __construct(
        public MoneyRequest $moneyRequest,
        public string $requesterName,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'warning';
    }

    protected function getNotificationIcon(): string
    {
        return 'hand-coins';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'Review Request';
    }

    protected function getNotificationTitle(): string
    {
        return 'New Money Request';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = formatPaisa($this->moneyRequest->amount);
        $expires = $this->moneyRequest->expires_at ? " (Expires: {$this->moneyRequest->expires_at->diffForHumans()})" : '';

        return "{$this->requesterName} has requested {$formatted} {$this->currency} from you.{$expires}";
    }

    protected function getNotificationData(): array
    {
        return [
            'money_request_id' => $this->moneyRequest->id,
            'amount' => $this->moneyRequest->amount,
            'requester_name' => $this->requesterName,
            'expires_at' => $this->moneyRequest->expires_at?->toIso8601String(),
            'has_hold' => (bool) $this->moneyRequest->hold_id,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('New Money Request Received', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('You can approve or decline this request directly from your wallet dashboard.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

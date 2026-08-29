<?php

namespace App\Notifications\Banking;

use App\Models\MoneyRequest;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MoneyRequestRejectedNotification extends Notification
{
    public function __construct(
        public MoneyRequest $moneyRequest,
        public string $payerName,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'error';
    }

    protected function getNotificationIcon(): string
    {
        return 'x-circle';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Requests';
    }

    protected function getNotificationTitle(): string
    {
        return 'Money Request Declined';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = formatPaisa($this->moneyRequest->amount);

        return "{$this->payerName} declined your request for {$formatted} {$this->currency}.";
    }

    protected function getNotificationData(): array
    {
        return [
            'money_request_id' => $this->moneyRequest->id,
            'amount' => $this->moneyRequest->amount,
            'payer_name' => $this->payerName,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Money Request Declined', $notifiable)
            ->line($this->getNotificationMessage());

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

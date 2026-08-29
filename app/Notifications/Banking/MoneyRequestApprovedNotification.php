<?php

namespace App\Notifications\Banking;

use App\Models\MoneyRequest;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MoneyRequestApprovedNotification extends Notification
{
    public function __construct(
        public MoneyRequest $moneyRequest,
        public string $payerName,
        public string $currency = 'BDT',
    ) {}

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
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Wallet';
    }

    protected function getNotificationTitle(): string
    {
        return 'Money Request Approved';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = number_format($this->moneyRequest->amount);

        return "{$this->payerName} approved your request for {$formatted} {$this->currency}. The funds are now in your wallet.";
    }

    protected function getNotificationData(): array
    {
        return [
            'money_request_id' => $this->moneyRequest->id,
            'transaction_id' => $this->moneyRequest->transaction_id,
            'amount' => $this->moneyRequest->amount,
            'payer_name' => $this->payerName,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Money Request Approved', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Your wallet balance has been updated with the requested amount.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

<?php

namespace App\Notifications\Banking;

use App\Models\MoneyRequest;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MoneyRequestExpiredNotification extends Notification
{
    public function __construct(
        public MoneyRequest $moneyRequest,
        public bool $isRequester = true,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'info';
    }

    protected function getNotificationIcon(): string
    {
        return 'clock';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View History';
    }

    protected function getNotificationTitle(): string
    {
        return 'Money Request Expired';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = number_format($this->moneyRequest->amount);

        if ($this->isRequester) {
            return "Your money request for {$formatted} {$this->currency} has expired without being fulfilled.";
        }

        return "A money request for {$formatted} {$this->currency} sent to you has expired. Any reserved hold on your balance has been released.";
    }

    protected function getNotificationData(): array
    {
        return [
            'money_request_id' => $this->moneyRequest->id,
            'amount' => $this->moneyRequest->amount,
            'is_requester' => $this->isRequester,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Money Request Expired', $notifiable)
            ->line($this->getNotificationMessage());

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

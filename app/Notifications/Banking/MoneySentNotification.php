<?php

namespace App\Notifications\Banking;

use App\Models\Transaction;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MoneySentNotification extends Notification
{
    public function __construct(
        public Transaction $transaction,
        public int $amount,
        public string $receiverName,
        public int $fee = 0,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'info';
    }

    protected function getNotificationIcon(): string
    {
        return 'arrow-up-right';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Transactions';
    }

    protected function getNotificationTitle(): string
    {
        return 'Money Sent';
    }

    protected function getNotificationMessage(): string
    {
        $formattedAmount = number_format($this->amount);
        $message = "You sent {$formattedAmount} {$this->currency} to {$this->receiverName}. (Ref: {$this->transaction->reference})";

        if ($this->fee > 0) {
            $formattedFee = number_format($this->fee);
            $message .= " Fee charged: {$formattedFee} {$this->currency}.";
        }

        return $message;
    }

    protected function getNotificationData(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->reference,
            'amount' => $this->amount,
            'fee' => $this->fee,
            'receiver_name' => $this->receiverName,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Money Sent Confirmation', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Your wallet balance has been updated accordingly.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

<?php

namespace App\Notifications\Banking;

use App\Models\Transaction;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MoneyReceivedNotification extends Notification
{
    public function __construct(
        public Transaction $transaction,
        public int $amount,
        public string $senderName,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'arrow-down-left';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Balance';
    }

    protected function getNotificationTitle(): string
    {
        return 'Money Received';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = formatPaisa($this->amount);

        return "You received {$formatted} {$this->currency} from {$this->senderName}. (Ref: {$this->transaction->reference})";
    }

    protected function getNotificationData(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->reference,
            'amount' => $this->amount,
            'sender_name' => $this->senderName,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Money Received in Your Wallet', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('The credited amount is now ready for use in your available balance.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

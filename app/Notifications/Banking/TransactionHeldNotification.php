<?php

namespace App\Notifications\Banking;

use App\Models\Transaction;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransactionHeldNotification extends Notification
{
    public function __construct(
        public Transaction $transaction,
        public int $amount,
        public string $reason = 'Security verification in progress',
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'warning';
    }

    protected function getNotificationIcon(): string
    {
        return 'lock';
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
        return 'Transaction Under Review';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = formatPaisa($this->amount);

        return "Your transaction of {$formatted} {$this->currency} (Ref: {$this->transaction->reference}) has been placed on temporary hold. Reason: {$this->reason}.";
    }

    protected function getNotificationData(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->reference,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Transaction Temporarily Held', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Our compliance system is reviewing this transaction to ensure safety. You will be notified once resolved.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

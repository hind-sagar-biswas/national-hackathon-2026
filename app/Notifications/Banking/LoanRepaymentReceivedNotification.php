<?php

namespace App\Notifications\Banking;

use App\Models\Loan;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanRepaymentReceivedNotification extends Notification
{
    public function __construct(
        public Loan $loan,
        public int $amount,
        public string $borrowerName,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'receipt';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Loan';
    }

    protected function getNotificationTitle(): string
    {
        return 'Loan Repayment Received';
    }

    protected function getNotificationMessage(): string
    {
        $formattedAmount = formatPaisa($this->amount);
        $remaining = formatPaisa($this->loan->outstanding_amount);

        return "{$this->borrowerName} paid {$formattedAmount} {$this->currency} towards Loan #{$this->loan->id}. Remaining balance: {$remaining} {$this->currency}.";
    }

    protected function getNotificationData(): array
    {
        return [
            'loan_id' => $this->loan->id,
            'amount_paid' => $this->amount,
            'outstanding_amount' => $this->loan->outstanding_amount,
            'borrower_name' => $this->borrowerName,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Loan Repayment Received', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('The repaid funds have been credited to your available balance.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

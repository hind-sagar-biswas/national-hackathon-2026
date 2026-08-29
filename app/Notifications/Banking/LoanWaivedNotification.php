<?php

namespace App\Notifications\Banking;

use App\Models\Loan;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanWaivedNotification extends Notification
{
    public function __construct(
        public Loan $loan,
        public string $lenderName,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'info';
    }

    protected function getNotificationIcon(): string
    {
        return 'heart-handshake';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Loans';
    }

    protected function getNotificationTitle(): string
    {
        return 'Loan Balance Waived';
    }

    protected function getNotificationMessage(): string
    {
        return "{$this->lenderName} has forgiven the remaining balance on Loan #{$this->loan->id}. You are no longer required to make further repayments for this loan.";
    }

    protected function getNotificationData(): array
    {
        return [
            'loan_id' => $this->loan->id,
            'lender_name' => $this->lenderName,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Loan Balance Waived', $notifiable)
            ->line($this->getNotificationMessage());

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

<?php

namespace App\Notifications\Banking;

use App\Models\Loan;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanSettledNotification extends Notification
{
    public function __construct(
        public Loan $loan,
        public string $otherPartyName,
        public bool $isLender = true,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'shield-check';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Completed Loan';
    }

    protected function getNotificationTitle(): string
    {
        return 'Loan Fully Settled';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = number_format($this->loan->principal_amount);

        if ($this->isLender) {
            return "Loan #{$this->loan->id} for {$formatted} {$this->currency} has been fully repaid by {$this->otherPartyName} and is now settled.";
        }

        return "Congratulations! You have completed all repayments for Loan #{$this->loan->id} ({$formatted} {$this->currency}) from {$this->otherPartyName}.";
    }

    protected function getNotificationData(): array
    {
        return [
            'loan_id' => $this->loan->id,
            'principal_amount' => $this->loan->principal_amount,
            'other_party_name' => $this->otherPartyName,
            'is_lender' => $this->isLender,
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Loan Settled in Full', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Thank you for using MaMoney lending services.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

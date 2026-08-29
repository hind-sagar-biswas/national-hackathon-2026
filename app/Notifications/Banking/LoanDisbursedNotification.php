<?php

namespace App\Notifications\Banking;

use App\Models\Loan;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanDisbursedNotification extends Notification
{
    public function __construct(
        public Loan $loan,
        public string $lenderName,
        public string $currency = 'BDT',
    ) {}

    protected function getNotificationType(): string
    {
        return 'success';
    }

    protected function getNotificationIcon(): string
    {
        return 'badge-dollar-sign';
    }

    protected function getActionUrl(): ?string
    {
        return route('dashboard');
    }

    protected function getActionText(): string
    {
        return 'View Loan Details';
    }

    protected function getNotificationTitle(): string
    {
        return 'Loan Disbursed to Your Wallet';
    }

    protected function getNotificationMessage(): string
    {
        $formatted = number_format($this->loan->principal_amount);
        $due = $this->loan->due_at ? " (Due: {$this->loan->due_at->format('M d, Y')})" : '';

        return "{$this->lenderName} has disbursed a loan of {$formatted} {$this->currency} to you.{$due}";
    }

    protected function getNotificationData(): array
    {
        return [
            'loan_id' => $this->loan->id,
            'principal_amount' => $this->loan->principal_amount,
            'lender_name' => $this->lenderName,
            'due_at' => $this->loan->due_at?->toIso8601String(),
            'currency' => $this->currency,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Loan Disbursed - MaMoney', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('The disbursed funds have been credited to your available balance.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

<?php

namespace App\Notifications\Banking;

use App\Models\BillSplit;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BillSplitFailedNotification extends Notification
{
    public function __construct(
        public BillSplit $billSplit,
        public string $reason,
        public bool $hadHoldReleased = false,
    ) {}

    protected function getNotificationType(): string
    {
        return 'danger';
    }

    protected function getNotificationIcon(): string
    {
        return 'alert-circle';
    }

    protected function getActionUrl(): ?string
    {
        return route('bill-splits.show', $this->billSplit->id);
    }

    protected function getActionText(): string
    {
        return 'View Details';
    }

    protected function getNotificationTitle(): string
    {
        return 'Bill Split Failed';
    }

    protected function getNotificationMessage(): string
    {
        $base = "Bill split \"{$this->billSplit->title}\" has failed ({$this->reason}).";
        if ($this->hadHoldReleased) {
            $base .= ' Any reserved funds held in your wallet have been fully restored.';
        }

        return $base;
    }

    protected function getNotificationData(): array
    {
        return [
            'bill_split_id' => $this->billSplit->id,
            'title' => $this->billSplit->title,
            'reason' => $this->reason,
            'had_hold_released' => $this->hadHoldReleased,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Bill Split Cancelled / Failed - MaMoney', $notifiable)
            ->line($this->getNotificationMessage());

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

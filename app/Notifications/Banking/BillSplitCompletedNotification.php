<?php

namespace App\Notifications\Banking;

use App\Models\BillSplit;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BillSplitCompletedNotification extends Notification
{
    public function __construct(
        public BillSplit $billSplit,
        public int $userShareAmount,
        public bool $isInitiator = false,
    ) {
        $this->billSplit->loadMissing('initiatorUser');
    }

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
        return route('bill-splits.show', $this->billSplit->id);
    }

    protected function getActionText(): string
    {
        return 'View Bill Split';
    }

    protected function getNotificationTitle(): string
    {
        return 'Bill Split Settled';
    }

    protected function getNotificationMessage(): string
    {
        $formattedTotal = formatPaisa($this->billSplit->total_amount);
        $formattedShare = formatPaisa($this->userShareAmount);

        if ($this->isInitiator) {
            return "All participants accepted \"{$this->billSplit->title}\"! Total {$formattedTotal} BDT has been successfully collected into your wallet.";
        }

        return "Bill split \"{$this->billSplit->title}\" has been completed. Your share of {$formattedShare} BDT was transferred to {$this->billSplit->initiatorUser->name}.";
    }

    protected function getNotificationData(): array
    {
        return [
            'bill_split_id' => $this->billSplit->id,
            'title' => $this->billSplit->title,
            'total_amount' => $this->billSplit->total_amount,
            'user_share_amount' => $this->userShareAmount,
            'is_initiator' => $this->isInitiator,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Bill Split Settled - MaMoney', $notifiable)
            ->line($this->getNotificationMessage());

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

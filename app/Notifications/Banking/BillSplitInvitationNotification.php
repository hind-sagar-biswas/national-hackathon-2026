<?php

namespace App\Notifications\Banking;

use App\Models\BillSplit;
use App\Models\BillSplitParticipant;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BillSplitInvitationNotification extends Notification
{
    public function __construct(
        public BillSplit $billSplit,
        public BillSplitParticipant $participant,
    ) {
        $this->billSplit->loadMissing('initiatorUser');
        $this->participant->loadMissing('user');
    }

    protected function getNotificationType(): string
    {
        return 'warning';
    }

    protected function getNotificationIcon(): string
    {
        return 'hand-coins';
    }

    protected function getActionUrl(): ?string
    {
        return route('bill-splits.show', $this->billSplit->id);
    }

    protected function getActionText(): string
    {
        return 'Review Split';
    }

    protected function getNotificationTitle(): string
    {
        return 'Bill Split Request';
    }

    protected function getNotificationMessage(): string
    {
        $initiatorName = $this->billSplit->initiatorUser->name;
        $formattedShare = formatPaisa($this->participant->share_amount);
        $formattedTotal = formatPaisa($this->billSplit->total_amount);

        return "{$initiatorName} invited you to split \"{$this->billSplit->title}\". Your share is {$formattedShare} BDT (Total: {$formattedTotal} BDT).";
    }

    protected function getNotificationData(): array
    {
        return [
            'bill_split_id' => $this->billSplit->id,
            'title' => $this->billSplit->title,
            'share_amount' => $this->participant->share_amount,
            'total_amount' => $this->billSplit->total_amount,
            'initiator_name' => $this->billSplit->initiatorUser->name,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->buildMailMessage('Bill Split Invitation - MaMoney', $notifiable)
            ->line($this->getNotificationMessage())
            ->line('Accepting this split will reserve your share on hold until all participants accept.');

        return $this->addActionButton($message);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

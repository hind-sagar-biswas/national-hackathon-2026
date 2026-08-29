<?php

namespace App\Notifications\Banking;

use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TransferOtpNotification extends Notification
{
    public function __construct(
        public string $otpCode,
        public int $expiresInMinutes = 5,
    ) {}

    protected function getNotificationType(): string
    {
        return 'warning';
    }

    protected function getNotificationIcon(): string
    {
        return 'key';
    }

    protected function getActionUrl(): ?string
    {
        return null;
    }

    protected function getNotificationTitle(): string
    {
        return 'Transaction Security OTP';
    }

    protected function getNotificationMessage(): string
    {
        return "Your one-time security verification code is {$this->otpCode}. It will expire in {$this->expiresInMinutes} minutes. Do not share this code with anyone.";
    }

    protected function getNotificationData(): array
    {
        return [
            'otp_code' => $this->otpCode,
            'expires_in_minutes' => $this->expiresInMinutes,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildMailMessage('Your Security Verification Code', $notifiable)
            ->line('A high-value or sensitive transfer was initiated on your account.')
            ->line("Your one-time verification code is: **{$this->otpCode}**")
            ->line("This code will expire in {$this->expiresInMinutes} minutes.")
            ->line('If you did not initiate this transaction, please change your password immediately.');
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildNotificationArray();
    }
}

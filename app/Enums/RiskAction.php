<?php

namespace App\Enums;

enum RiskAction: string
{
    case ALLOW = 'allow';
    case NOTIFY = 'notify';
    case CHALLENGE_OTP = 'challenge_otp';
    case HOLD = 'hold';
    case BLOCK = 'block';

    public function label(): string
    {
        return match ($this) {
            self::ALLOW => 'Allow Automatically',
            self::NOTIFY => 'Allow with Notification',
            self::CHALLENGE_OTP => 'Require OTP Challenge',
            self::HOLD => 'Place on Hold for Review',
            self::BLOCK => 'Block Transaction',
        };
    }
}

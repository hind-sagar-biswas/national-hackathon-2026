<?php

namespace App\Enums;

enum BillSplitParticipantStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted (Funds Held)',
            self::REJECTED => 'Rejected',
        };
    }
}

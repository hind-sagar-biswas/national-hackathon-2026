<?php

namespace App\Enums;

enum BillSplitStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Acceptances',
            self::COMPLETED => 'Completed & Settled',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled by Initiator',
        };
    }
}

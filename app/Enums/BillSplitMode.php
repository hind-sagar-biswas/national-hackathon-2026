<?php

namespace App\Enums;

enum BillSplitMode: string
{
    case EQUAL = 'equal';
    case PERCENTAGE = 'percentage';
    case WEIGHTS = 'weights';

    public function label(): string
    {
        return match ($this) {
            self::EQUAL => 'Equal Split',
            self::PERCENTAGE => 'Percentage Split',
            self::WEIGHTS => 'Weighted Split',
        };
    }
}

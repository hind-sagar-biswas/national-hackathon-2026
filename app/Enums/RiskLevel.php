<?php

namespace App\Enums;

enum RiskLevel: string
{
    case LOW = 'low';
    case MODERATE = 'moderate';
    case MOD_HIGH = 'mod_high';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low Risk',
            self::MODERATE => 'Moderate Risk',
            self::MOD_HIGH => 'Moderate-High Risk',
            self::HIGH => 'High Risk',
        };
    }
}

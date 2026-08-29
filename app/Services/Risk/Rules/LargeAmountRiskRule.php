<?php

namespace App\Services\Risk\Rules;

use App\Models\LedgerEntry;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

class LargeAmountRiskRule implements RiskRuleInterface
{
    public function getIdentifier(): string
    {
        return 'large_amount_risk';
    }

    public function getName(): string
    {
        return 'Large Amount Threshold Check';
    }

    public function evaluate(RiskContextDTO $context): int
    {
        $amount = $context->amount;
        $points = 0;

        // Absolute thresholds
        if ($amount >= 80000) {
            $points += 50;
        } elseif ($amount >= 50000) {
            $points += 30;
        } elseif ($amount >= 25000) {
            $points += 15;
        }

        // Relative deviation from historical average
        $historicalAvg = LedgerEntry::where('account_id', $context->senderAccount->id)->avg('amount');
        if ($historicalAvg && $historicalAvg > 0 && $amount > ($historicalAvg * 3.5)) {
            $points += 20;
        }

        return min(100, $points);
    }

    public function getReason(RiskContextDTO $context): ?string
    {
        $formatted = number_format($context->amount);

        if ($context->amount >= 50000) {
            return "High value transfer of {$formatted} BDT exceeds standard thresholds.";
        }

        return null;
    }
}

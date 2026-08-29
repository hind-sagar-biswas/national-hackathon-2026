<?php

namespace App\Services\Risk\Rules;

use App\Models\LedgerEntry;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

/**
 * Risk rule evaluating absolute transfer values and relative deviation from historical averages.
 */
class LargeAmountRiskRule implements RiskRuleInterface
{
    /**
     * Unique identifier for this rule.
     */
    public function getIdentifier(): string
    {
        return 'large_amount_risk';
    }

    /**
     * Human-readable rule name.
     */
    public function getName(): string
    {
        return 'Large Amount Threshold Check';
    }

    /**
     * Evaluate context and return points to add to risk score (0 to 100).
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return int Points contributed by this rule
     */
    public function evaluate(RiskContextDTO $context): int
    {
        $amount = $context->amount;
        $points = 0;

        if ($amount >= 8000000) {
            $points += 50;
        } elseif ($amount >= 5000000) {
            $points += 30;
        } elseif ($amount >= 2500000) {
            $points += 15;
        }

        $historicalAvg = LedgerEntry::where('account_id', $context->senderAccount->id)->avg('amount');
        if ($historicalAvg && $historicalAvg > 0 && $amount > ($historicalAvg * 3.5)) {
            $points += 20;
        }

        return min(100, $points);
    }

    /**
     * Optional detailed explanation when this large amount rule matches.
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return string|null Reason text if triggered
     */
    public function getReason(RiskContextDTO $context): ?string
    {
        $formatted = formatPaisa($context->amount);

        if ($context->amount >= 5000000) {
            return "High value transfer of {$formatted} BDT exceeds standard thresholds.";
        }

        return null;
    }
}

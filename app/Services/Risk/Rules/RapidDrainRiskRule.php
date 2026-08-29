<?php

namespace App\Services\Risk\Rules;

use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

/**
 * Risk rule evaluating sudden significant drainage of available wallet balance and new account age penalties.
 */
class RapidDrainRiskRule implements RiskRuleInterface
{
    /**
     * Unique identifier for this rule.
     */
    public function getIdentifier(): string
    {
        return 'rapid_drain_risk';
    }

    /**
     * Human-readable rule name.
     */
    public function getName(): string
    {
        return 'Rapid Balance Drain Check';
    }

    /**
     * Evaluate context and return points to add to risk score (0 to 100).
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return int Points contributed by this rule
     */
    public function evaluate(RiskContextDTO $context): int
    {
        $senderAccount = $context->senderAccount;
        $currentBalance = $senderAccount->cleared_balance;

        if ($currentBalance <= 0) {
            return 0;
        }

        $drainRatio = $context->amount / $currentBalance;

        $points = 0;

        if ($drainRatio >= 0.90) {
            $points += 35;
        } elseif ($drainRatio >= 0.75) {
            $points += 20;
        }

        if ($senderAccount->created_at && $senderAccount->created_at->diffInHours(now()) < 6) {
            $points += 15;
        }

        return min(100, $points);
    }

    /**
     * Optional detailed explanation when this rapid drain rule matches.
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return string|null Reason text if triggered
     */
    public function getReason(RiskContextDTO $context): ?string
    {
        $senderAccount = $context->senderAccount;
        $currentBalance = $senderAccount->cleared_balance;

        if ($currentBalance > 0 && ($context->amount / $currentBalance) >= 0.75) {
            $percentage = round(($context->amount / $currentBalance) * 100);

            return "Attempting to drain {$percentage}% of total available account balance.";
        }

        return null;
    }
}

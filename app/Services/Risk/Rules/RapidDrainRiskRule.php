<?php

namespace App\Services\Risk\Rules;

use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

class RapidDrainRiskRule implements RiskRuleInterface
{
    public function getIdentifier(): string
    {
        return 'rapid_drain_risk';
    }

    public function getName(): string
    {
        return 'Rapid Balance Drain Check';
    }

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

        // Fresh account penalty (account created in last 6 hours)
        if ($senderAccount->created_at && $senderAccount->created_at->diffInHours(now()) < 6) {
            $points += 15;
        }

        return min(100, $points);
    }

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

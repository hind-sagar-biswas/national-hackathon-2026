<?php

namespace App\Services\Risk\Rules;

use App\Models\LedgerEntry;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

class VelocityRiskRule implements RiskRuleInterface
{
    public function getIdentifier(): string
    {
        return 'velocity_risk';
    }

    public function getName(): string
    {
        return 'High Frequency Velocity Check';
    }

    public function evaluate(RiskContextDTO $context): int
    {
        $senderAccountId = $context->senderAccount->id;

        // Count recent debits in last 15 minutes
        $recentFifteenMinCount = LedgerEntry::where('account_id', $senderAccountId)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentFifteenMinCount >= 5) {
            return 45;
        }

        if ($recentFifteenMinCount >= 3) {
            return 25;
        }

        // Count in last 1 hour
        $recentOneHourCount = LedgerEntry::where('account_id', $senderAccountId)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentOneHourCount >= 10) {
            return 30;
        }

        return 0;
    }

    public function getReason(RiskContextDTO $context): ?string
    {
        $senderAccountId = $context->senderAccount->id;
        $count = LedgerEntry::where('account_id', $senderAccountId)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($count >= 3) {
            return "High transaction frequency detected: {$count} transactions in the last 15 minutes.";
        }

        return null;
    }
}

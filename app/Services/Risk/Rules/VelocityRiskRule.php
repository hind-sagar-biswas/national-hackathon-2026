<?php

namespace App\Services\Risk\Rules;

use App\Models\LedgerEntry;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

/**
 * Risk rule evaluating outbound transaction frequency and velocity within short time windows.
 */
class VelocityRiskRule implements RiskRuleInterface
{
    /**
     * Unique identifier for this rule.
     */
    public function getIdentifier(): string
    {
        return 'velocity_risk';
    }

    /**
     * Human-readable rule name.
     */
    public function getName(): string
    {
        return 'High Frequency Velocity Check';
    }

    /**
     * Evaluate context and return points to add to risk score (0 to 100).
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return int Points contributed by this rule
     */
    public function evaluate(RiskContextDTO $context): int
    {
        $senderAccountId = $context->senderAccount->id;

        $recentFifteenMinCount = LedgerEntry::where('account_id', $senderAccountId)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentFifteenMinCount >= 5) {
            return 45;
        }

        if ($recentFifteenMinCount >= 3) {
            return 25;
        }

        $recentOneHourCount = LedgerEntry::where('account_id', $senderAccountId)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentOneHourCount >= 10) {
            return 30;
        }

        return 0;
    }

    /**
     * Optional detailed explanation when this velocity rule matches.
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return string|null Reason text if triggered
     */
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

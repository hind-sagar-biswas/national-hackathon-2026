<?php

namespace App\Services\Risk\Rules;

use App\Models\OperationEvent;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

/**
 * Risk rule evaluating whether a transfer is being sent to a new, unfamiliar beneficiary for the first time.
 */
class NewBeneficiaryRiskRule implements RiskRuleInterface
{
    /**
     * Unique identifier for this rule.
     */
    public function getIdentifier(): string
    {
        return 'new_beneficiary_risk';
    }

    /**
     * Human-readable rule name.
     */
    public function getName(): string
    {
        return 'First-Time Beneficiary Transfer Check';
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
        $receiverAccountId = $context->receiverAccount->id;

        $hasTransferredBefore = OperationEvent::where('from_account_id', $senderAccountId)
            ->where('to_account_id', $receiverAccountId)
            ->exists();

        if (! $hasTransferredBefore && $context->amount >= 10000) {
            return 15;
        }

        return 0;
    }

    /**
     * Optional detailed explanation when this new beneficiary rule matches.
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return string|null Reason text if triggered
     */
    public function getReason(RiskContextDTO $context): ?string
    {
        $senderAccountId = $context->senderAccount->id;
        $receiverAccountId = $context->receiverAccount->id;

        $hasTransferredBefore = OperationEvent::where('from_account_id', $senderAccountId)
            ->where('to_account_id', $receiverAccountId)
            ->exists();

        if (! $hasTransferredBefore && $context->amount >= 10000) {
            return 'First-time transfer to an unfamiliar beneficiary account.';
        }

        return null;
    }
}

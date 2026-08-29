<?php

namespace App\Services\Risk\Rules;

use App\Models\OperationEvent;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskContextDTO;

class NewBeneficiaryRiskRule implements RiskRuleInterface
{
    public function getIdentifier(): string
    {
        return 'new_beneficiary_risk';
    }

    public function getName(): string
    {
        return 'First-Time Beneficiary Transfer Check';
    }

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

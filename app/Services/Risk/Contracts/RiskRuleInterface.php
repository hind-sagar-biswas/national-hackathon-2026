<?php

namespace App\Services\Risk\Contracts;

use App\Services\Risk\DTOs\RiskContextDTO;

/**
 * Interface defining contract for pluggable transaction risk assessment rules.
 */
interface RiskRuleInterface
{
    /**
     * Unique identifier for this rule.
     */
    public function getIdentifier(): string;

    /**
     * Human-readable rule name.
     */
    public function getName(): string;

    /**
     * Evaluate context and return points to add to risk score (0 to 100).
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return int Points contributed by this rule (0 to 100)
     */
    public function evaluate(RiskContextDTO $context): int;

    /**
     * Optional detailed reason when rule matches.
     *
     * @param  RiskContextDTO  $context  The transaction context
     * @return string|null Reason text if triggered
     */
    public function getReason(RiskContextDTO $context): ?string;
}

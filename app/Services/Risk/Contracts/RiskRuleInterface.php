<?php

namespace App\Services\Risk\Contracts;

use App\Services\Risk\DTOs\RiskContextDTO;

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
     * @return int Points contributed by this rule
     */
    public function evaluate(RiskContextDTO $context): int;

    /**
     * Optional detailed reason when rule matches.
     */
    public function getReason(RiskContextDTO $context): ?string;
}

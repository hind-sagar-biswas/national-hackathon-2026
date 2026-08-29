<?php

namespace App\Services\Risk;

use App\Enums\RiskAction;
use App\Enums\RiskLevel;
use App\Services\Risk\Contracts\RiskRuleInterface;
use App\Services\Risk\DTOs\RiskAssessmentResult;
use App\Services\Risk\DTOs\RiskContextDTO;
use App\Services\Risk\Rules\LargeAmountRiskRule;
use App\Services\Risk\Rules\NewBeneficiaryRiskRule;
use App\Services\Risk\Rules\RapidDrainRiskRule;
use App\Services\Risk\Rules\VelocityRiskRule;

class RiskEvaluationService
{
    /** @var array<RiskRuleInterface> */
    protected array $rules;

    /**
     * @param  array<RiskRuleInterface>|null  $rules
     */
    public function __construct(?array $rules = null)
    {
        $this->rules = $rules ?? [
            new VelocityRiskRule,
            new LargeAmountRiskRule,
            new RapidDrainRiskRule,
            new NewBeneficiaryRiskRule,
        ];
    }

    /**
     * Add a custom or dynamic rule to the pipeline.
     */
    public function addRule(RiskRuleInterface $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Evaluate context across all rules and compute risk score & action.
     */
    public function evaluate(RiskContextDTO $context): RiskAssessmentResult
    {
        $totalScore = 0;
        $matchedRules = [];

        foreach ($this->rules as $rule) {
            $points = $rule->evaluate($context);

            if ($points > 0) {
                $totalScore += $points;
                $matchedRules[] = [
                    'rule' => $rule->getIdentifier(),
                    'name' => $rule->getName(),
                    'points' => $points,
                    'reason' => $rule->getReason($context),
                ];
            }
        }

        $totalScore = min(100, $totalScore);

        [$level, $action] = $this->determineLevelAndAction($totalScore);

        return new RiskAssessmentResult(
            score: $totalScore,
            level: $level,
            action: $action,
            matchedRules: $matchedRules,
        );
    }

    /**
     * Map aggregated risk score to RiskLevel and RiskAction.
     *
     * @return array{0: RiskLevel, 1: RiskAction}
     */
    protected function determineLevelAndAction(int $score): array
    {
        return match (true) {
            $score >= 85 => [RiskLevel::HIGH, RiskAction::HOLD],
            $score >= 60 => [RiskLevel::MOD_HIGH, RiskAction::CHALLENGE_OTP],
            $score >= 30 => [RiskLevel::MODERATE, RiskAction::NOTIFY],
            default => [RiskLevel::LOW, RiskAction::ALLOW],
        };
    }
}

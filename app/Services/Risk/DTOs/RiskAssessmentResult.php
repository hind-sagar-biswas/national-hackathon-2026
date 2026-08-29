<?php

namespace App\Services\Risk\DTOs;

use App\Enums\RiskAction;
use App\Enums\RiskLevel;

/**
 * Data Transfer Object representing the output of a risk evaluation pipeline.
 */
readonly class RiskAssessmentResult
{
    /**
     * Create a new RiskAssessmentResult instance.
     *
     * @param  int  $score  Aggregated risk score from 0 to 100
     * @param  RiskLevel  $level  Categorized risk severity level (LOW, MODERATE, MOD_HIGH, HIGH)
     * @param  RiskAction  $action  Recommended system action (ALLOW, NOTIFY, CHALLENGE_OTP, HOLD, BLOCK)
     * @param  array<int, array{rule: string, name: string, points: int, reason: string}>  $matchedRules  List of matched rules contributing to the risk score
     */
    public function __construct(
        public int $score,
        public RiskLevel $level,
        public RiskAction $action,
        public array $matchedRules = [],
    ) {}

    /**
     * Determine if transaction is allowed to proceed without blocking or holding.
     */
    public function isAllowed(): bool
    {
        return $this->action === RiskAction::ALLOW || $this->action === RiskAction::NOTIFY;
    }

    /**
     * Determine if an asynchronous security notice should be dispatched to the user.
     */
    public function shouldNotify(): bool
    {
        return $this->action === RiskAction::NOTIFY;
    }

    /**
     * Determine if a stepped-up OTP challenge is required before proceeding.
     */
    public function requiresChallenge(): bool
    {
        return $this->action === RiskAction::CHALLENGE_OTP;
    }

    /**
     * Determine if the transaction funds must be placed on a compliance hold.
     */
    public function shouldHold(): bool
    {
        return $this->action === RiskAction::HOLD;
    }

    /**
     * Determine if the transaction should be rejected outright.
     */
    public function isBlocked(): bool
    {
        return $this->action === RiskAction::BLOCK;
    }

    /**
     * Convert the assessment result to a serializable array representation.
     *
     * @return array{score: int, level: string, action: string, matched_rules: array<int, array{rule: string, name: string, points: int, reason: string}>}
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'level' => $this->level->value,
            'action' => $this->action->value,
            'matched_rules' => $this->matchedRules,
        ];
    }
}

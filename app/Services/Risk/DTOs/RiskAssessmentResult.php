<?php

namespace App\Services\Risk\DTOs;

use App\Enums\RiskAction;
use App\Enums\RiskLevel;

readonly class RiskAssessmentResult
{
    /**
     * @param  int  $score  Score from 0 to 100
     * @param  RiskLevel  $level  Calculated risk level
     * @param  RiskAction  $action  Recommended system action
     * @param  array<string, mixed>  $matchedRules  Details of rules that added risk points
     */
    public function __construct(
        public int $score,
        public RiskLevel $level,
        public RiskAction $action,
        public array $matchedRules = [],
    ) {}

    public function isAllowed(): bool
    {
        return $this->action === RiskAction::ALLOW || $this->action === RiskAction::NOTIFY;
    }

    public function shouldNotify(): bool
    {
        return $this->action === RiskAction::NOTIFY;
    }

    public function requiresChallenge(): bool
    {
        return $this->action === RiskAction::CHALLENGE_OTP;
    }

    public function shouldHold(): bool
    {
        return $this->action === RiskAction::HOLD;
    }

    public function isBlocked(): bool
    {
        return $this->action === RiskAction::BLOCK;
    }

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

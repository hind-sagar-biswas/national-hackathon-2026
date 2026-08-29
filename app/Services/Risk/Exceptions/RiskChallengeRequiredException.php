<?php

namespace App\Services\Risk\Exceptions;

use App\Services\Risk\DTOs\RiskAssessmentResult;
use RuntimeException;

class RiskChallengeRequiredException extends RuntimeException
{
    public function __construct(
        public readonly RiskAssessmentResult $assessment,
        string $message = 'Security verification required to complete this transaction.',
        int $code = 428,
    ) {
        parent::__construct($message, $code);
    }
}

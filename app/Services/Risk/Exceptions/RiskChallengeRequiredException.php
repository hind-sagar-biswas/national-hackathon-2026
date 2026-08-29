<?php

namespace App\Services\Risk\Exceptions;

use App\Services\Risk\DTOs\RiskAssessmentResult;
use RuntimeException;

/**
 * Exception thrown when a transaction evaluation triggers a stepped-up security challenge (e.g. OTP verification).
 */
class RiskChallengeRequiredException extends RuntimeException
{
    /**
     * Create a new RiskChallengeRequiredException instance.
     *
     * @param  RiskAssessmentResult  $assessment  The structured risk assessment result
     * @param  string  $message  Error explanation message
     * @param  int  $code  HTTP status code representation (428 Precondition Required)
     */
    public function __construct(
        public readonly RiskAssessmentResult $assessment,
        string $message = 'Security verification required to complete this transaction.',
        int $code = 428,
    ) {
        parent::__construct($message, $code);
    }
}

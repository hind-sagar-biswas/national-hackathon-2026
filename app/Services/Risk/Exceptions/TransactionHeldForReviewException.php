<?php

namespace App\Services\Risk\Exceptions;

use App\Models\Hold;
use App\Services\Risk\DTOs\RiskAssessmentResult;
use RuntimeException;

/**
 * Exception thrown when a transaction evaluation triggers an automated compliance hold for review.
 */
class TransactionHeldForReviewException extends RuntimeException
{
    /**
     * Create a new TransactionHeldForReviewException instance.
     *
     * @param  RiskAssessmentResult  $assessment  The structured risk assessment result
     * @param  Hold|null  $hold  The created balance hold entity
     * @param  string  $message  Error explanation message
     * @param  int  $code  HTTP status code representation (202 Accepted)
     */
    public function __construct(
        public readonly RiskAssessmentResult $assessment,
        public readonly ?Hold $hold = null,
        string $message = 'This transaction has been temporarily held for security review.',
        int $code = 202,
    ) {
        parent::__construct($message, $code);
    }
}

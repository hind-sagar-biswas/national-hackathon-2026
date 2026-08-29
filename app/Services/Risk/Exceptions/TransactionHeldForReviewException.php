<?php

namespace App\Services\Risk\Exceptions;

use App\Models\Hold;
use App\Services\Risk\DTOs\RiskAssessmentResult;
use RuntimeException;

class TransactionHeldForReviewException extends RuntimeException
{
    public function __construct(
        public readonly RiskAssessmentResult $assessment,
        public readonly ?Hold $hold = null,
        string $message = 'This transaction has been temporarily held for security review.',
        int $code = 202,
    ) {
        parent::__construct($message, $code);
    }
}

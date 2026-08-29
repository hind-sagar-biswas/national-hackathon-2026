<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(string $message = 'Insufficient available balance to complete this transaction.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}

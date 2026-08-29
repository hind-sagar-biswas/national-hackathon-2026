<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case INITIATED = 'initiated';
    case COMPLETED = 'completed';
    case HELD = 'held';
    case FAILED = 'failed';
    case REVERTED = 'reverted';
}

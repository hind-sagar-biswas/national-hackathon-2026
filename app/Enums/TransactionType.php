<?php

namespace App\Enums;

enum TransactionType: string
{
    case REG_BONUS = 'registration bonus';
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER = 'transfer';
    case REQ_SETTLEMENT = 'request settlement';
}

<?php

namespace App\Enums;

enum TransactionType: string
{
    case REGISTRATION_BONUS = 'registration_bonus';
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER = 'transfer';
    case TRANSFER_WITH_FEE = 'transfer_with_fee';
    case REQUEST_SETTLEMENT = 'request_settlement';
    case LOAN_DISBURSEMENT = 'loan_disbursement';
    case LOAN_REPAYMENT = 'loan_repayment';
    case REVERSAL = 'reversal';
    case ADJUSTMENT = 'adjustment';
}

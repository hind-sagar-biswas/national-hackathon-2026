<?php

namespace App\Enums;

enum LoanStatus: string
{
    case ACTIVE = 'active';
    case PARTIAL = 'partial';
    case SETTLED = 'settled';
    case WAIVED = 'waived';
}

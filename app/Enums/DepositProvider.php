<?php

namespace App\Enums;

enum DepositProvider: string
{
    case BKASH = 'bkash';
    case NAGAD = 'nagad';
    case BANK = 'bank';
}

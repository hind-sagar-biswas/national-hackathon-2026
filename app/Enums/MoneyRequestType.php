<?php

namespace App\Enums;

enum MoneyRequestType: string
{
    case STANDARD = 'standard';
    case LOAN = 'loan';
}

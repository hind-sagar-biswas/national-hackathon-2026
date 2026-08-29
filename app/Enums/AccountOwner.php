<?php

namespace App\Enums;

enum AccountOwner: string
{
    case SYSTEM = 'system';
    case USER = 'user';
}

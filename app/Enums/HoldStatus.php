<?php

namespace App\Enums;

enum HoldStatus: string
{
    case ACTIVE = 'active';
    case CAPTURED = 'captured';
    case RELEASED = 'released';
}

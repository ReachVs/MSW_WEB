<?php

namespace App\Enums;

enum MechanicStatus: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Off = 'off';
}

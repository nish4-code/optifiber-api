<?php

namespace App\Enums;

enum ClosureType: string
{
    case Splice = 'empalme';
    case Distribution = 'distribucion';
    case Reserve = 'reserva';
}

<?php

namespace App\Enums;

enum PortStatus: string
{
    case Free = 'libre';
    case Occupied = 'ocupado';
    case Reserved = 'reservado';
    case Damaged = 'dañado';
}

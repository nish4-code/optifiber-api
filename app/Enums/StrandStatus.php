<?php

namespace App\Enums;

enum StrandStatus: string
{
    case Free = 'libre';
    case Occupied = 'ocupado';
    case Reserved = 'reservado';
    case Damaged = 'averiado';
}

<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Active = 'activo';
    case Pending = 'pendiente';
    case Suspended = 'suspendido';
}

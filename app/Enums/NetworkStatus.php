<?php

namespace App\Enums;

enum NetworkStatus: string
{
    case Active = 'activo';
    case Alert = 'alerta';
    case Maintenance = 'mantenimiento';
    case Inactive = 'inactivo';
}

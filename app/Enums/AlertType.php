<?php

namespace App\Enums;

enum AlertType: string
{
    case NapCapacity = 'capacidad_nap';
    case OnuSignal = 'senal_onu';
    case Maintenance = 'mantenimiento';
    case Installation = 'instalacion';
}

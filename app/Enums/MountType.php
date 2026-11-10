<?php

namespace App\Enums;

enum MountType: string
{
    case Pole = 'poste';
    case Chamber = 'camara';
    case Aerial = 'aereo';
    case Underground = 'subterraneo';
}

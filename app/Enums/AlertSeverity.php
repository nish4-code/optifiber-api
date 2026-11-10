<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case Critical = 'critico';
    case Warning = 'advertencia';
    case Info = 'info';
}

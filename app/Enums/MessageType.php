<?php

namespace App\Enums;

enum MessageType: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case VALIDATION = 'validation';
}

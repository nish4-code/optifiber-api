<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class SmartOltApiException extends Exception
{
    public function __construct(string $message = 'Error en la comunicación con SmartOLT', int $code = 502)
    {
        parent::__construct($message, $code);
    }
}


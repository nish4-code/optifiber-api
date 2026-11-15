<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class SmartOltRateLimitException extends Exception
{
    protected int $retryAfter;

    public function __construct(string $message = 'Límite de solicitudes de SmartOLT alcanzado (HTTP 429)', int $retryAfter = 60)
    {
        parent::__construct($message, 429);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}


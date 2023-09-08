<?php

declare(strict_types=1);

namespace App\Http\CircuitBreaker\Exceptions;

use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

use function sprintf;

final class NotAvailableException extends RuntimeException implements GuzzleException
{
    public function __construct(string $service)
    {
        parent::__construct(
            sprintf('Circuit breaker not available for service: "%s"', $service),
        );
    }
}

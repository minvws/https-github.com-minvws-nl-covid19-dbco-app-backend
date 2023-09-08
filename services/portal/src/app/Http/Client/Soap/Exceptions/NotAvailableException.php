<?php

declare(strict_types=1);

namespace App\Http\Client\Soap\Exceptions;

use RuntimeException;

final class NotAvailableException extends RuntimeException implements SoapClientException
{
    public static function circuitBreakerOpen(): self
    {
        return new self('circuit breaker open');
    }
}

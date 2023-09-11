<?php

declare(strict_types=1);

namespace App\Http\CircuitBreaker\Exceptions;

use RuntimeException;

use function sprintf;

final class ServiceNameNotConfiguredException extends RuntimeException
{
    public static function missingOption(string $optionKey): self
    {
        $message = sprintf('No "%s" option configured for client', $optionKey);

        return new self($message);
    }
}

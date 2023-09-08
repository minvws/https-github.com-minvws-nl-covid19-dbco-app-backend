<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception;

use RuntimeException;

use function gettype;
use function sprintf;

class AssignmentInvalidValueException extends RuntimeException implements AssignmentException
{
    public static function wrongType(string $name, string $expectedType, mixed $value): self
    {
        return new self(sprintf(
            'Invalid type for value "%s" given. Expected a "%s", but got a "%s"',
            $name,
            $expectedType,
            gettype($value),
        ));
    }
}

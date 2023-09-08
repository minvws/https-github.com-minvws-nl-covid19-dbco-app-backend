<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class InvalidCaseAssignmentException extends UnprocessableEntityHttpException
{
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message ?? "Ongeldige toewijzing", $previous, $code, $headers);
    }
}

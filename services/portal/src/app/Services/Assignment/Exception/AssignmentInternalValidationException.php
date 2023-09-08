<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception;

use Exception;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Use this exception if you use a Laravel validator to validate internal state, but dont want to expose the detailed
 * error messages to the end-user, but when the debug modus is enabled it will expose the original validation response.
 *
 * See the exception handler map in AssignmentServiceProvider to find out how this is done.
 *
 * @method ValidationException getPrevious()
 */
class AssignmentInternalValidationException extends Exception implements AssignmentException, HttpExceptionInterface
{
    public function __construct(
        string $message,
        ValidationException $validationException,
        public readonly int $status = 500,
        public readonly array $headers = [],
    ) {
        parent::__construct($message, previous: $validationException);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}

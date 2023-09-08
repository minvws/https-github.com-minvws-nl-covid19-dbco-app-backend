<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class SkipTestResultImportException extends RuntimeException implements SkippableTestResultReportImportException
{
    protected function __construct(string $message)
    {
        parent::__construct($message);
    }

    final public static function messageAlreadyProcessed(): self
    {
        return new self('Message has already been processed');
    }
}

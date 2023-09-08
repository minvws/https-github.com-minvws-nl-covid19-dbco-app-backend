<?php

declare(strict_types=1);

namespace App\Services\Export\Exceptions;

use Throwable;

trait ExportExceptionBase
{
    final public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function from(Throwable $previous, ?string $message = null): static
    {
        if ($previous instanceof static && $message === null) {
            return $previous;
        }

        return new static($message ?? $previous->getMessage(), $previous);
    }
}

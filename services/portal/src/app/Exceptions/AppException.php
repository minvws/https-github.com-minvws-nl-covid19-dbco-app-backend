<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

use function is_int;

abstract class AppException extends Exception
{
    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromThrowable(Throwable $throwable): static
    {
        if ($throwable instanceof static) {
            return $throwable;
        }

        $code = is_int($throwable->getCode()) ? $throwable->getCode() : 0;
        return new static($throwable->getMessage(), $code, $throwable);
    }
}

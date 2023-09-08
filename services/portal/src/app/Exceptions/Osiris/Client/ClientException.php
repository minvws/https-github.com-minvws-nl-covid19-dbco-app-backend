<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris\Client;

use Throwable;

final class ClientException extends BaseClientException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        $code = $previous ? $previous->getCode() : 0;

        parent::__construct($message, $code, $previous);
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        return new self($throwable->getMessage(), $throwable);
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

use function sprintf;

final class CouldNotWriteFile extends RuntimeException
{
    public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Could not write file: %s', $path), $code, $previous);
    }
}

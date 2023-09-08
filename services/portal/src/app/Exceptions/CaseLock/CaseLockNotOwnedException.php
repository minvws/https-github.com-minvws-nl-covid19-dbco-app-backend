<?php

declare(strict_types=1);

namespace App\Exceptions\CaseLock;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class CaseLockNotOwnedException extends BadRequestHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 400, array $headers = [])
    {
        parent::__construct("Not the owner of the CaseLock", $previous, $code, $headers);
    }
}

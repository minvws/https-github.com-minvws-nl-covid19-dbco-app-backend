<?php

declare(strict_types=1);

namespace App\Exceptions\CaseLock;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class CaseLockFoundException extends BadRequestHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 400, array $headers = [])
    {
        parent::__construct("CaseLock found", $previous, $code, $headers);
    }
}

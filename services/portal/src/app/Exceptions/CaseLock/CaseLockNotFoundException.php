<?php

declare(strict_types=1);

namespace App\Exceptions\CaseLock;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CaseLockNotFoundException extends NotFoundHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 404, array $headers = [])
    {
        parent::__construct("CaseLock not found", $previous, $code, $headers);
    }
}

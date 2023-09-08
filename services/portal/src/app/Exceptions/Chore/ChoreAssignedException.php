<?php

declare(strict_types=1);

namespace App\Exceptions\Chore;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class ChoreAssignedException extends BadRequestHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 400, array $headers = [])
    {
        parent::__construct("Chore already as an assignment", $previous, $code, $headers);
    }
}

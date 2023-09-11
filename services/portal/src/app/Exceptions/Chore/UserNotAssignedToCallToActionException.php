<?php

declare(strict_types=1);

namespace App\Exceptions\Chore;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class UserNotAssignedToCallToActionException extends BadRequestHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 403, array $headers = [])
    {
        parent::__construct('User is not assigned to CallToAction', $previous, $code, $headers);
    }
}

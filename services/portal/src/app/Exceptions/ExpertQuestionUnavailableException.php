<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Throwable;

class ExpertQuestionUnavailableException extends GoneHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct('Expert Question is niet (meer) beschikbaar', $previous, $code, $headers);
    }
}

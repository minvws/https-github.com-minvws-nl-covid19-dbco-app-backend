<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class AttachmentNotFoundHttpException extends NotFoundHttpException
{
    public function __construct(?Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct("Deze bijlage is niet gevonden in de db", $previous, $code, $headers);
    }
}

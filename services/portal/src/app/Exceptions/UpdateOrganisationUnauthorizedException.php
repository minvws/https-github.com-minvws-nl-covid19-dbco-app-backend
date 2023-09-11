<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Validation\UnauthorizedException;
use Throwable;

final class UpdateOrganisationUnauthorizedException extends UnauthorizedException
{
    public function __construct(string $message = 'Unauthorized', ?Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}

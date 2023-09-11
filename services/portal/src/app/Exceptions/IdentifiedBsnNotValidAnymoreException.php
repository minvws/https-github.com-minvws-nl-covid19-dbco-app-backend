<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class IdentifiedBsnNotValidAnymoreException extends Exception
{
    public const EXCEPTION_MESSAGE = 'Identified Bsn could not be matched with found Bsn';

    public function __construct()
    {
        parent::__construct(self::EXCEPTION_MESSAGE, 409);
    }
}

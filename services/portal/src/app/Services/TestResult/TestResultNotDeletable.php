<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestResultNotDeletable extends HttpException
{
    public static function incorrectSource(): self
    {
        return new self(Response::HTTP_FORBIDDEN, "Only possible to delete TestResults created in BCO Portal");
    }

    public static function caseMismatch(): self
    {
        return new self(Response::HTTP_BAD_REQUEST, "TestResult not part of Case");
    }
}

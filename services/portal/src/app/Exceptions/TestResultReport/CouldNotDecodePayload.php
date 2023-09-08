<?php

declare(strict_types=1);

namespace App\Exceptions\TestResultReport;

use Exception;

final class CouldNotDecodePayload extends Exception
{
    public function __construct()
    {
        parent::__construct('Could not decode payload');
    }
}

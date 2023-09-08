<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception;

use RuntimeException;

class AssignmentRuntimeException extends RuntimeException implements AssignmentException
{
}

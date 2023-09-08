<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception;

use InvalidArgumentException;

class AssignmentInvalidArgumentException extends InvalidArgumentException implements AssignmentException
{
}

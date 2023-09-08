<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception;

use UnexpectedValueException;

class AssignmentUnexpectedValueException extends UnexpectedValueException implements AssignmentException
{
}

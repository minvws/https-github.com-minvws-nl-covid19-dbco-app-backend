<?php

declare(strict_types=1);

namespace App\Services\Assignment\Exception;

use DomainException;

class AssignmentDomainException extends DomainException implements AssignmentException
{
}

<?php

declare(strict_types=1);

namespace App\Services\Export\Exceptions;

use RuntimeException;

class ExportRuntimeException extends RuntimeException
{
    use ExportExceptionBase;
}

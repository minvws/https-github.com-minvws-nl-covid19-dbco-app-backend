<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris\CaseExport;

use Exception;

abstract class BaseCaseExportException extends Exception implements CaseExportExceptionInterface
{
}

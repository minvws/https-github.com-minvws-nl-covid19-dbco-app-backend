<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Marker interface to silently abort the import of a test result report.
 */
interface SkippableTestResultReportImportException extends Throwable
{
}

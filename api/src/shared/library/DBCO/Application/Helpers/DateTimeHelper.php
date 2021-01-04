<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Helpers;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * Utility methods for helping with dates/times.
 *
 * This abstraction allows easy mocking of the current/date time.
 *
 * @package DBCO\Shared\Application\Helpers
 */
class DateTimeHelper
{
    /**
     * Returns the current date/time as object.
     *
     * @param DateTimeZone|null $timeZone
     *
     * @return DateTimeImmutable
     *
     * @throws Exception
     */
    public function now(?DateTimeZone $timeZone = null): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $timeZone);
    }
}

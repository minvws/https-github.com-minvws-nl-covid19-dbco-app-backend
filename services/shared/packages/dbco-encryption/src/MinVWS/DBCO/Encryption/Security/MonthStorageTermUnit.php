<?php

namespace MinVWS\DBCO\Encryption\Security;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

use function sprintf;

/**
 * Represents a month unit.
 *
 * @package MinVWS\DBCO\Encryption\Security
 */
final class MonthStorageTermUnit extends StorageTermUnit
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function __construct(DateTimeInterface $date, string $prefix = null)
    {
        $this->date = new DateTimeImmutable($date->format('Y-m') . '-01');
        $this->prefix = $prefix;
    }

    /**
     * @inheritDoc
     */
    protected function getInterval(int $length): DateInterval
    {
        return new DateInterval('P' . $length . 'M');
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return sprintf('%s%s', $this->prefix, $this->date->format('Ym'));
    }
}

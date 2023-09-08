<?php

namespace MinVWS\DBCO\Encryption\Security;

use DateInterval;
use DateTimeImmutable;

/**
 * Represents a unit within a term period.
 *
 * @package MinVWS\DBCO\Encryption\Security
 */
abstract class StorageTermUnit
{
    protected DateTimeImmutable $date;
    protected ?string $prefix;

    abstract public function __construct(DateTimeImmutable $date, string $prefix = null);

    /**
     * Returns an interval of the given length.
     *
     * @param int $length Interval length.
     *
     * @return DateInterval
     */
    abstract protected function getInterval(int $length): DateInterval;

    /**
     * Returns a string representation for this unit.
     *
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Adds the given amount of units.
     *
     * The returned unit is a different instance.
     *
     * @param int $amount
     *
     * @return static
     */
    public function add(int $amount): StorageTermUnit
    {
        return new static($this->date->add($this->getInterval($amount)), $this->prefix);
    }

    /**
     * Substract the given amount of units.
     *
     * The returned unit is a different instance.
     *
     * @param int $amount
     *
     * @return static
     */
    public function sub(int $amount): StorageTermUnit
    {
        return new static($this->date->sub($this->getInterval($amount)), $this->prefix);
    }

    /**
     * Next unit.
     *
     * The returned unit is a different instance.
     *
     * @return static
     */
    public function next(): StorageTermUnit
    {
        return $this->add(1);
    }

    /**
     * Previous unit.
     *
     * The returned unit is a different instance.
     *
     * @return static
     */
    public function previous(): StorageTermUnit
    {
        return $this->sub(1);
    }

    /**
     * Checks if two term units are equal.
     *
     * @param static $other
     *
     * @return bool
     */
    public function equals(StorageTermUnit $other): bool
    {
        return (string)$this === (string)$other;
    }
}

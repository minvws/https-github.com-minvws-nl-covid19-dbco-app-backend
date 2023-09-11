<?php

namespace MinVWS\DBCO\Encryption\Security;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final class StorageTerm
{
    public const VERY_SHORT = 'very_short';
    public const SHORT = 'short';
    public const LONG  = 'long';

    private static ?StorageTerm $veryShort = null;
    private static ?StorageTerm $short = null;
    private static ?StorageTerm $long = null;

    private static array $activeIntervals = [
        self::VERY_SHORT => 3,
        self::SHORT => 28,
        self::LONG  => 60,
    ];

    public static function veryShort(): StorageTerm
    {
        if (StorageTerm::$veryShort === null) {
            StorageTerm::$veryShort = new StorageTerm(self::VERY_SHORT);
        }

        return StorageTerm::$veryShort;
    }

    /**
     * Short term.
     *
     * @return StorageTerm
     */
    public static function short(): StorageTerm
    {
        if (StorageTerm::$short === null) {
            StorageTerm::$short = new StorageTerm(self::SHORT);
        }

        return StorageTerm::$short;
    }

    /**
     * Long term.
     *
     * @return StorageTerm
     */
    public static function long(): StorageTerm
    {
        if (StorageTerm::$long === null) {
            StorageTerm::$long = new StorageTerm(self::LONG);
        }

        return StorageTerm::$long;
    }

    /**
     * Returns the storage term for the given constant value.
     *
     * If the string is not recognized, this method defaults to short.
     *
     * @param string $value
     *
     * @return StorageTerm
     */
    public static function forValue(string $value): StorageTerm
    {
        switch ($value) {
            case self::LONG:
                return self::long();
            case self::SHORT:
                return self::short();
            case self::VERY_SHORT:
                return self::veryShort();
            default:
                throw new EncryptionException('invalid value');
        }
    }

    /**
     * @var string
     */
    private string $term;

    /**
     * Constructor.
     *
     * @param string $term
     */
    private function __construct(string $term)
    {
        $this->term = $term;
    }

    /**
     * String representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->term;
    }

    /**
     * Creates a term unit for the given date time.
     *
     * A unit represents a single day or month for the given term.
     * E.g. a long term uses months as units and a short term uses
     * days as units.
     *
     * @param DateTimeInterface $dateTime
     *
     * @return StorageTermUnit
     */
    public function unitForDateTime(DateTimeInterface $dateTime): StorageTermUnit
    {
        switch ($this) {
            case StorageTerm::long():
                return new MonthStorageTermUnit($dateTime);
            case StorageTerm::short():
                return new DayStorageTermUnit($dateTime);
            case StorageTerm::veryShort():
                return new DayStorageTermUnit($dateTime, 'VS');
            default:
                throw new EncryptionException('invalid storageTerm');
        }
    }

    /**
     * Calculates the expiration date for the encryption based on the given reference date.
     *
     * @param DateTimeInterface $referenceDate
     *
     * @return DateTimeInterface
     */
    public function expirationDateForReferenceDate(DateTimeInterface $referenceDate): DateTimeInterface
    {
        $ref = new CarbonImmutable($referenceDate);

        switch ($this) {
            case StorageTerm::long():
                $ref = $ref->addMonths($this->getActiveInterval());
                break;
            case StorageTerm::short():
            case StorageTerm::veryShort():
                $ref = $ref->addDays($this->getActiveInterval());
                break;
            default:
                throw new EncryptionException('invalid storageTerm');
        }

        return $ref->toDateTimeImmutable();
    }

    /**
     * Returns the interval in which a storage key remains active.
     *
     * The interval's unit is based on the storage term.
     *
     * @return int
     */
    public function getActiveInterval(): int
    {
        return self::$activeIntervals[$this->term];
    }
}

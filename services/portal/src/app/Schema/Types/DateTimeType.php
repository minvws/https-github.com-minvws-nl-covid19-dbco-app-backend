<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Generator\JSONSchema\Context;
use DateTimeInterface;
use DateTimeZone;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;

use function assert;
use function is_null;

/**
 * Date time type.
 *
 * Accepts and returns date/time instances that implement the DateTimeInterface. No guarantees
 * are made for the internally used date/time class.
 */
class DateTimeType extends Type
{
    public const FORMAT_DATE = 'Y-m-d';
    public const FORMAT_TIME = 'H:i:sp';
    public const FORMAT_DATETIME = self::FORMAT_DATE . '\T' . self::FORMAT_TIME;

    private string $format;
    private DateTimeZone $timeZone;

    /**
     * @param string $format Date/time format, defaults to ISO-8601.
     * @param DateTimeZone|null $timeZone Time zone, defaults to UTC.
     */
    final public function __construct(string $format = self::FORMAT_DATETIME, ?DateTimeZone $timeZone = null)
    {
        parent::__construct();

        $this->format = $format;
        $this->timeZone = $timeZone ?? new DateTimeZone('UTC');

        $this->getValidationRules()
            ->addFatal('string')
            ->addFatal('date_format:' . $format);
    }

    /**
     * Returns the date/time format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    public function getTimeZone(): DateTimeZone
    {
        return $this->timeZone;
    }

    public function isOfType(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function encode(EncodingContainer $container, mixed $value): void
    {
        assert(is_null($value) || $value instanceof DateTimeInterface);
        $container->encodeDateTime($value, $this->format, $this->timeZone);
    }

    public function decode(DecodingContainer $container, mixed $current): ?DateTimeInterface
    {
        return $container->decodeDateTimeIfPresent($this->format, $this->timeZone);
    }

    public function getAnnotationType(): string
    {
        return '\\' . DateTimeInterface::class;
    }

    public function getTypeScriptAnnotationType(): string
    {
        return 'Date';
    }

    public function toJSONSchema(Context $context): array
    {
        $format = match ($this->format) {
            self::FORMAT_DATETIME => 'date-time',
            self::FORMAT_DATE => 'date',
            self::FORMAT_TIME => 'time',
            default => null
        };

        $schema = ['type' => 'string'];
        if ($format !== null) {
            $schema['format'] = $format;
        }

        return $schema;
    }

    /**
     * Create field using the date/time type.
     *
     * @param string $name Field name.
     * @param string $format Date/time format, defaults to ISO-8601.
     * @param DateTimeZone|null $timeZone Time zone, defaults to UTC.
     */
    public static function createField(string $name, string $format = 'Y-m-d\TH:i:sp', ?DateTimeZone $timeZone = null): Field
    {
        return new Field($name, new static($format, $timeZone));
    }

    /**
     * Create array field with date/time type elements.
     *
     * @param string $name Field name.
     * @param string $format Date/time format, defaults to ISO-8601.
     * @param DateTimeZone|null $timeZone Time zone, defaults to UTC.
     */
    public static function createArrayField(string $name, string $format = 'Y-m-d\TH:i:sp', ?DateTimeZone $timeZone = null): ArrayField
    {
        return new ArrayField($name, new static($format, $timeZone));
    }
}

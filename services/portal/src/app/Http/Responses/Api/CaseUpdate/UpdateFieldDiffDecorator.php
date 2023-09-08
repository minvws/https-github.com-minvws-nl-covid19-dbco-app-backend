<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\CaseUpdate;

use App\Schema\Fields\Field;
use App\Schema\Fields\SchemaVersionField;
use App\Schema\SchemaObject;
use App\Schema\Types\ArrayType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use App\Schema\Update\UpdateFieldDiff;
use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;

use function assert;
use function config;
use function is_array;

class UpdateFieldDiffDecorator implements EncodableDecorator
{
    public const ID_PREFIX = 'idPrefix';

    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof UpdateFieldDiff);

        $container->id = $container->getContext()->getValue(self::ID_PREFIX) . $value->getField()->getName();
        $this->encodeFieldInfo($value->getField(), $container);
        $this->encodeFieldValue($value->getField(), $container->nestedContainer('oldValue'), $value->getOldValue());
        $this->encodeFieldValue($value->getField(), $container->nestedContainer('newValue'), $value->getNewValue());
        $this->encodeFieldDisplayValue($value->getField(), $container->nestedContainer('oldDisplayValue'), $value->getOldValue());
        $this->encodeFieldDisplayValue($value->getField(), $container->nestedContainer('newDisplayValue'), $value->getNewValue());
    }

    private function encodeFieldInfo(Field $field, EncodingContainer $container): void
    {
        $container->name = $field->getName();
        $container->label = $field->getDocumentation()->getLabel() ?? $field->getName();
    }

    /**
     * @throws CodableException
     */
    private function encodeFieldValue(Field $field, EncodingContainer $container, mixed $value): void
    {
        $field->getType()->encode($container, $value);
    }

    /**
     * @throws CodableException
     */
    private function encodeFieldDisplayValue(Field $field, EncodingContainer $container, mixed $value): void
    {
        $container->getContext()->setMode(EncodingContext::MODE_DISPLAY);
        $this->encodeTypeDisplayValue($field->getType(), $container, $value);
    }

    /**
     * @throws CodableException
     */
    private function encodeTypeDisplayValue(Type $type, EncodingContainer $container, mixed $value): void
    {
        if ($value === null) {
            $container->encodeNull();
            return;
        }

        // custom display encoder has precedence
        $encoder = $type->getEncoder($container->getContext()->getMode(), false);
        if ($encoder !== null) {
            $type->encode($container, $value);
            return;
        }

        if ($type instanceof ArrayType && is_array($value)) {
            $this->encodeArrayDisplayValue($value, $container, $type->getElementType());
            return;
        }

        if ($type instanceof SchemaType && $value instanceof SchemaObject) {
            $this->encodeSchemaObjectDisplayValue($value, $container);
            return;
        }

        if ($type instanceof DateTimeType && $value instanceof DateTimeInterface) {
            $this->encodeDateTimeDisplayValue($value, $container, $type->getFormat());
            return;
        }

        $type->encode($container, $value);
    }

    private function encodeArrayDisplayValue(array $array, EncodingContainer $container, Type $elementType): void
    {
        $container->encodeArray($array, function (EncodingContainer $elementContainer, $value) use ($elementType): void {
            $this->encodeTypeDisplayValue($elementType, $elementContainer, $value);
        });
    }

    private function encodeSchemaObjectDisplayValue(SchemaObject $object, EncodingContainer $container): void
    {
        $schemaVersion = $object->getSchemaVersion();
        $container->label = $schemaVersion->getDocumentation()->getLabel() ?? $schemaVersion->getSchema()->getName();

        $fields = [];
        foreach ($schemaVersion->getFields() as $field) {
            if (!$field instanceof SchemaVersionField && $field->assignedValue($object) !== null) {
                $fields[] = $field;
            }
        }

        $container->fields->encodeArray($fields, function (EncodingContainer $fieldContainer, Field $field) use ($object): void {
            $value = $field->assignedValue($object);
            $this->encodeFieldInfo($field, $fieldContainer);
            $this->encodeFieldValue($field, $fieldContainer->nestedContainer('value'), $value);
            $this->encodeFieldDisplayValue($field, $fieldContainer->nestedContainer('displayValue'), $value);
        });
    }

    private function encodeDateTimeDisplayValue(DateTimeInterface $dateTime, EncodingContainer $container, string $valueFormat): void
    {
        $timeFormat = $valueFormat === 'Y-m-d' ? IntlDateFormatter::NONE : IntlDateFormatter::SHORT;
        $tz = new DateTimeZone(config('app.display_timezone'));
        $formatter = IntlDateFormatter::create(config('app.locale'), IntlDateFormatter::LONG, $timeFormat, $tz);
        $container->encode($formatter ? $formatter->format($dateTime) ?: null : null);
    }
}

<?php

declare(strict_types=1);

namespace App\Schema\Fields;

use App\Models\Fields\Pseudonymizer;
use App\Schema\SchemaObject;
use App\Schema\Types\StringType;
use Closure;
use MinVWS\Codable\EncodingContext;

use function assert;
use function call_user_func;
use function is_array;
use function is_callable;
use function is_string;

class PseudonomizedField extends DerivedField
{
    /**
     * @param Closure(SchemaObject,EncodingContext):mixed $valueCallback gets the value that needs to be pseudonymized
     */
    public function __construct(
        string $name,
        Closure $valueCallback,
    ) {
        parent::__construct(
            $name,
            new StringType(),
            static function (SchemaObject $object, EncodingContext $context) use ($valueCallback): mixed {
                assert(is_callable($valueCallback));
                $value = call_user_func($valueCallback, $object, $context);

                if ($value === null) {
                    return null;
                }

                assert(is_string($value));
                return Pseudonymizer::pseudonimizeForContext($value, $context);
            },
        );
    }

    public static function createFromField(string $name, string $sourceFieldName): PseudonomizedField
    {
        return new self(
            $name,
            static function (SchemaObject $object) use ($sourceFieldName): mixed {
                return $object->getSchemaVersion()->getExpectedField($sourceFieldName)->assignedValue($object);
            },
        );
    }

    public static function createFromNestedField(string $name, string $nestedField, string $nestedFieldKey): PseudonomizedField
    {
        return new self(
            $name,
            static function (SchemaObject $object) use ($nestedField, $nestedFieldKey): mixed {
                $nestedFieldData = $object->getSchemaVersion()->getExpectedField($nestedField)->assignedValue($object);
                assert(is_array($nestedFieldData) || $nestedFieldData === null);
                return $nestedFieldData[$nestedFieldKey] ?? null;
            },
        );
    }
}

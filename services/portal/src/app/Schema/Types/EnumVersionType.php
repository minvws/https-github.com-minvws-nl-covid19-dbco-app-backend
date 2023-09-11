<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Generator\JSONSchema\Context;
use App\Schema\Generator\JSONSchema\EnumVersionBuilder;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use Illuminate\Validation\Rule;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\Enum;
use MinVWS\DBCO\Enum\Models\EnumVersion;

use function in_array;
use function ucfirst;

class EnumVersionType extends Type
{
    private EnumVersion $enumVersion;

    final public function __construct(EnumVersion $enumVersion)
    {
        parent::__construct();

        $this->enumVersion = $enumVersion;

        $this->getValidationRules()
            ->addFatal('string')
            ->addFatal(static fn() => Rule::in($enumVersion->allValues()));

        $this->setEncoder(
            static fn (EncodingContainer $container, ?Enum $value) => $container->encode($value->label ?? null),
            EncodingContext::MODE_DISPLAY,
        );
    }

    public function getEnumVersion(): EnumVersion
    {
        return $this->enumVersion;
    }

    public function isOfType(mixed $value): bool
    {
        return in_array($value, $this->getEnumVersion()->all(), true);
    }

    public function decode(DecodingContainer $container, mixed $current): ?Enum
    {
        return $this->enumVersion->decode($container);
    }

    public function getAnnotationType(): string
    {
        return '\\' . $this->getEnumVersion()->getEnumClass();
    }

    public function getTypeScriptAnnotationType(): string
    {
        $className = $this->getEnumVersion()->getEnumClass();
        $tsConst = $className::tsConst();
        if (!$tsConst) {
            return 'any';
        }
        return ucfirst($tsConst) . 'V' . $this->getEnumVersion()->getVersion();
    }

    public function toJSONSchema(Context $context): array
    {
        $ref = $context->getRefForEnumVersion($this->enumVersion);
        if ($context->getUseCompoundSchemas() === UseCompoundSchemas::No) {
            return ['$ref' => $ref];
        }

        $name = $context->getNameForEnumVersion($this->enumVersion);
        if (!$context->defs->contains($name)) {
            $builder = new EnumVersionBuilder($this->enumVersion);
            $context->defs->put($name, $builder->buildDef($context));
        }

        return ['$ref' => $ref];
    }

    /**
     * Create field using the enum type for the given enum version.
     *
     * @param string $name Field name.
     * @param EnumVersion $enumVersion Version for enum (e.g. $enumClass::getVersion(...).
     */
    public static function createField(string $name, EnumVersion $enumVersion): Field
    {
        return new Field($name, new static($enumVersion));
    }

    /**
     * Create array field using the enum type for the given enum version.
     *
     * @param string $name Field name.
     * @param EnumVersion $enumVersion Version for enum (e.g. $enumClass::getVersion(...).
     */
    public static function createArrayField(string $name, EnumVersion $enumVersion): ArrayField
    {
        return new ArrayField($name, new static($enumVersion));
    }
}

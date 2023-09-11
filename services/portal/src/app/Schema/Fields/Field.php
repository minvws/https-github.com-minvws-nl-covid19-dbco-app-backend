<?php

declare(strict_types=1);

namespace App\Schema\Fields;

use App\Models\Purpose\Purpose;
use App\Schema\Conditions\Condition;
use App\Schema\Documentation\Traits\HasDocumentation;
use App\Schema\Generator\JSONSchema\Context;
use App\Schema\Purpose\PurposeSpecification;
use App\Schema\Purpose\PurposeSpecificationConfig;
use App\Schema\Purpose\SubPurpose;
use App\Schema\Purpose\Traits\HasPurposeSpecification;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use Closure;
use InvalidArgumentException;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use RuntimeException;

use function array_diff;
use function array_filter;
use function array_merge;
use function array_unique;
use function call_user_func;
use function count;
use function get_class;
use function in_array;
use function is_a;
use function is_callable;
use function is_null;
use function sprintf;

/**
 * Represents a field in a schema that holds a value of a certain type.
 *
 * Fields by default:
 * - allow null values
 * - support all versions of a schema
 * - apply basic validation rules based on their type
 * - support encoding/decoding based on their type
 *
 * @template T of Type
 */
class Field
{
    use HasDocumentation;
    use HasPurposeSpecification;

    private string $name;

    /** @var T */
    private Type $type;

    private ?Schema $schema = null;

    private int $minVersion = 1;

    private ?int $maxVersion = null;

    private bool $hasDefaultValue = false;

    /** @var mixed */
    private $defaultValue = null;

    private bool $allowsNull = true;

    private bool $isExternal = false;

    private bool $includedInEncode = true;

    /** @var array<string, bool> */
    private array $includedInEncodeByMode = [];

    private ?Condition $encodingCondition = null;

    /** @var array<string, Condition> */
    private array $encodingConditionByMode = [];

    private bool $includedInDecode = true;

    /** @var array<bool> */
    private array $includedInDecodeByMode = [];

    private ?Condition $decodingCondition = null;

    /** @var array<string, Condition> */
    private array $decodingConditionByMode = [];

    private ?string $proxyForOwnerField = null;

    private ValidationRules $validationRules;

    private array $views = [];

    /**
     * @param T $type
     */
    public function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->validationRules = new ValidationRules();

        $this->getValidationRules()
            ->addChild($type->getValidationRules());

        $this->setAllowsNull(true);
    }

    /**
     * Returns the schema this field belongs to.
     *
     * WARNING: Don't call this method before adding the field to a schema!
     */
    public function getSchema(): Schema
    {
        if ($this->schema === null) {
            throw new RuntimeException(
                sprintf('%s should not be called before adding the field to a schema!', __METHOD__),
            );
        }

        return $this->schema;
    }

    protected function getFallbackPurposeSpecification(): ?PurposeSpecification
    {
        return $this->getSchema()->getPurposeSpecification();
    }

    protected function getFallbackSubPurposeOverride(): ?SubPurpose
    {
        return PurposeSpecificationConfig::getConfig()->getFallbackSubPurposeOverrideForType($this->getType()::class);
    }

    /**
     * Sets the schema for this field.
     *
     * NOTE: This method is called automatically when a field is added to a schema.
     */
    public function setSchema(Schema $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * The field name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The field type.
     *
     * @return T
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * The field type.
     *
     * Guarantees that the returned type is of the given expected type, otherwise throws an exception.
     * Useful for method chaining using generics.
     *
     * @template E of Type
     *
     * @param class-string<E> $expectedType
     *
     * @return E
     */
    public function getExpectedType(string $expectedType): Type
    {
        if (!is_a($this->type, $expectedType)) {
            throw new InvalidArgumentException(
                "Expected type \"{$expectedType}\" does not match field type \"" . get_class($this->type) . "\"!",
            );
        }

        return $this->type;
    }

    /**
     * Modify type.
     *
     * @param Closure $modifier Closure is called with this field's type as argument.
     */
    public function modifyType(Closure $modifier): void
    {
        $modifier($this->getType());
    }

    protected function getDefaultDocumentationIdentifier(): string
    {
        return $this->getSchema()->getDocumentationIdentifier() . '.' . $this->getName();
    }

    /**
     * @inheritdoc
     */
    protected function getDocumentationIdentifiers(): array
    {
        $identifiers = [];
        foreach ($this->getSchema()->getDocumentation()->getIdentifiers() as $identifier) {
            $identifiers[] = $identifier . '.' . $this->getName() . '.v' . $this->getMinVersion();
            $identifiers[] = $identifier . '.' . $this->getName();
        }

        return $identifiers;
    }

    /**
     * Set default value.
     *
     * A value of null does not reset the result of Field::hasDefaultValue(), which will still be true. To really
     * reset the default value, use Field::resetDefaultValue().
     *
     * If you provide a callable as value it will be called lazily when an default value is needed.
     *
     * @return $this
     */
    public function setDefaultValue(mixed $value): self
    {
        $this->hasDefaultValue = true;
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * Reset default value.
     *
     * After this call the Field::hasDefaultValue() will return false.
     *
     * @return $this
     */
    public function resetDefaultValue(): self
    {
        $this->hasDefaultValue = false;
        $this->defaultValue = null;
        return $this;
    }

    /**
     * Is there a default value set?
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * Returns the default value, if set, null otherwise.
     *
     * If the default value has been set to a callable the result of the callable will be returned.
     *
     * A return value of null does not necessarily mean no default value has been set (the default value can be
     * explicitly set to null). To check if an default value has been set use Field::hasDefaultValue().
     */
    public function getDefaultValue(): mixed
    {
        return is_callable($this->defaultValue) ? call_user_func($this->defaultValue, $this) : $this->defaultValue;
    }

    /**
     * Allows null values (defaults to true).
     *
     * @return $this
     */
    public function setAllowsNull(bool $allowsNull): self
    {
        $this->allowsNull = $allowsNull;

        if ($allowsNull) {
            $this->getValidationRules()->addRule('nullable', ValidationRules::ALL, [ValidationRule::TAG_ALWAYS]);
        } else {
            $this->getValidationRules()->removeRule('nullable');
        }

        return $this;
    }

    /**
     * Allows null?
     */
    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    /**
     * Set minimum version of schema that contains this field.
     *
     * @return $this
     */
    public function setMinVersion(int $minVersion): self
    {
        $this->minVersion = $minVersion;
        return $this;
    }

    /**
     * Minimum version of schema that contains this field.
     */
    public function getMinVersion(): int
    {
        return $this->minVersion;
    }

    /**
     * Set maximum version of schema that contains this field.
     *
     * @return $this
     */
    public function setMaxVersion(?int $maxVersion): self
    {
        $this->maxVersion = $maxVersion;
        return $this;
    }

    /**
     * Maximum version of schema that contains this field.
     */
    public function getMaxVersion(): ?int
    {
        return $this->maxVersion;
    }

    /**
     * Checks if this field is part of the given schema version.
     */
    public function isInVersion(int $version): bool
    {
        return $version >= $this->getMinVersion() && ($this->getMaxVersion() === null || $version <= $this->getMaxVersion());
    }

    public function addToView(string $name): self
    {
        $this->views = array_unique([...$this->views, $name]);
        return $this;
    }

    public function removeFromView(string $name): self
    {
        $this->views = array_diff($this->views, [$name]);
        return $this;
    }

    public function isInView(string $name): bool
    {
        return in_array($name, $this->views, true);
    }

    /**
     * Set customer value encoder.
     *
     * @param callable $encoder Encoder.
     * @param string|null $mode EncodingContext::MODE_STORE / EncodingContext::MODE_OUTPUT
     */
    public function setEncoder(callable $encoder, ?string $mode = null): self
    {
        $this->getType()->setEncoder($encoder, $mode);
        return $this;
    }

    /**
     * Returns the encoder for the given mode.
     *
     * @param string|null $mode Encoding mode.
     * @param bool $fallback Fallback to general encoder (if registered).
     */
    public function getEncoder(?string $mode, bool $fallback = true): ?callable
    {
        return $this->getType()->getEncoder($mode, $fallback);
    }

    /**
     * Set inclusion for encode.
     *
     * @return $this
     */
    public function setIncludedInEncode(bool $included, ?string $mode = null): self
    {
        if ($mode === null) {
            $this->includedInEncode = $included;
            $this->includedInEncodeByMode = [];
        } else {
            $this->includedInEncodeByMode[$mode] = $included;
        }

        return $this;
    }

    /**
     * Should this field be encoded?
     */
    public function isIncludedInEncode(?string $mode): bool
    {
        return $this->includedInEncodeByMode[$mode] ?? $this->includedInEncode;
    }

    /**
     * Encode value.
     *
     * @throws CodableException
     */
    protected function encodeValue(EncodingContainer $container, mixed $value, SchemaObject $source): void
    {
        $container->getContext()->setValue(SchemaObject::class, $source);
        $this->getType()->encode($container, $value);
    }

    /**
     * Sets the condition for when this field should be included in encoding.
     *
     * @param Condition $condition Condition.
     * @param string|null $mode Encoding mode.
     */
    public function setEncodingCondition(Condition $condition, ?string $mode = null): self
    {
        if ($mode === null) {
            $this->encodingCondition = $condition;
            $this->encodingConditionByMode = [];
        } else {
            $this->encodingConditionByMode[$mode] = $condition;
        }

        return $this;
    }

    /**
     * Returns the encoding condition for this field.
     *
     * @param string|null $mode Encoding mode.
     */
    public function getEncodingCondition(?string $mode): ?Condition
    {
        return $this->encodingConditionByMode[$mode] ?? $this->encodingCondition;
    }

    /**
     * Encode field.
     *
     * @throws CodableException
     */
    public function encode(EncodingContainer $container, SchemaObject $source): void
    {
        $nestedContainer = $container->{$this->getName()};
        $value = $source->{$this->getName()} ?? null;

        $this->encodeValue($nestedContainer, $value, $source);
    }

    /**
     * Set decoder.
     *
     * @param callable $decoder Decoder.
     * @param string|null $mode DecodingContext::MODE_LOAD / DecodingContext::MODE_INPUT
     *
     * @return $this
     */
    public function setDecoder(callable $decoder, ?string $mode = null): self
    {
        $this->getType()->setDecoder($decoder, $mode);
        return $this;
    }

    /**
     * Get decoder for the given mode.
     */
    public function getDecoder(?string $mode): ?callable
    {
        return $this->getType()->getDecoder($mode);
    }

    /**
     * Set inclusion for decode.
     *
     * @return $this
     */
    public function setIncludedInDecode(bool $included, ?string $mode = null): self
    {
        if ($mode === null) {
            $this->includedInDecode = $included;
            $this->includedInDecodeByMode = [];
        } else {
            $this->includedInDecodeByMode[$mode] = $included;
        }

        return $this;
    }

    /**
     * Should this field be decoded?
     */
    public function isIncludedInDecode(?string $mode): bool
    {
        return $this->includedInDecodeByMode[$mode] ?? $this->includedInDecode;
    }

    /**
     * Sets the condition for when this field should be included in decoding.
     *
     * @param Condition $condition Condition.
     * @param string|null $mode Decoding mode.
     */
    public function setDecodingCondition(Condition $condition, ?string $mode = null): self
    {
        if ($mode === null) {
            $this->decodingCondition = $condition;
            $this->decodingConditionByMode = [];
        } else {
            $this->decodingConditionByMode[$mode] = $condition;
        }

        return $this;
    }

    /**
     * Returns the decoding condition for this field.
     *
     * @param string|null $mode Decoding mode.
     */
    public function getDecodingCondition(?string $mode): ?Condition
    {
        return $this->decodingConditionByMode[$mode] ?? $this->decodingCondition;
    }

    /**
     * Initialize this field for a new instance.
     */
    public function init(SchemaObject $target): void
    {
        if (!$this->hasDefaultValue()) {
            return;
        }

        $target->{$this->getName()} = $this->getDefaultValue();
    }

    /**
     * Decode value.
     *
     * @throws CodableException
     */
    public function decodeValue(DecodingContainer $container, mixed $current): mixed
    {
        return $this->getType()->decode($container, $current);
    }

    /**
     * Check if this field will be decoded.
     *
     * Field should be included for decoding for the decoding context mode and should
     * be part of the decoding container.
     */
    public function willDecode(DecodingContainer $container): bool
    {
        $mode = $container->getContext()->getMode();
        return $container->contains($this->getName()) && $this->isIncludedInDecode($mode);
    }

    /**
     * Decode field into the given target.
     *
     * @param DecodingContainer $container Decoding container for the complete object.
     * @param SchemaObject $target Target object.
     *
     * @throws CodableException
     */
    public function decode(DecodingContainer $container, object $target): void
    {
        if (!$this->willDecode($container)) {
            return;
        }

        if (!$container->contains($this->getName())) {
            return;
        }

        $nestedContainer = $container->{$this->getName()};
        $current = $this->assignedValue($target);

        $target->{$this->getName()} = $this->decodeValue($nestedContainer, $current);
    }

    /**
     * Sets the condition for when this field should be included in encoding/decoding.
     *
     * To have more granular control you can use `setEncodingCondition` and `setDecodingCondition`.
     *
     * @param Condition $condition Condition.
     *
     * @return $this
     */
    public function setCodingCondition(Condition $condition): self
    {
        $this->setEncodingCondition($condition);
        $this->setDecodingCondition($condition);
        return $this;
    }

    /**
     * Assign value for this field on the given target.
     *
     * @param object $target Target object.
     * @param mixed $value Value
     *
     * @throws InvalidArgumentException
     */
    public function assign(object $target, mixed $value): void
    {
        if ($value === null && !$this->allowsNull()) {
            throw new InvalidArgumentException("Null value is not allowed for field \"{$this->getName()}\"");
        }

        if ($value !== null && !$this->getType()->isOfType($value)) {
            throw new InvalidArgumentException(
                "Value does not conform to {$this->getType()->getAnnotationType()} for field \"{$this->getName()}\"",
            );
        }

        $target->{$this->getName()} = $value;
    }

    /**
     * Returns the assigned value on the given target object.
     */
    public function assignedValue(object $target): mixed
    {
        return $target->{$this->getName()} ?? null;
    }

    /**
     * Compare the values for this field for the given objects.
     */
    public function valuesForObjectsEqual(SchemaObject $object1, SchemaObject $object2): bool
    {
        return $this->valuesEqual(
            $this->assignedValue($object1),
            $this->assignedValue($object2),
        );
    }

    /**
     * Compare 2 values for this field.
     */
    public function valuesEqual(mixed $value1, mixed $value2): bool
    {
        return $this->getType()->valuesEqual($value1, $value2);
    }

    /**
     * Is data for this field managed externally?
     */
    public function isExternal(): bool
    {
        return $this->isExternal;
    }

    /**
     * Data for this field is managed externally.
     *
     * @return $this
     */
    public function setExternal(): self
    {
        $this->isExternal = true;

        return
            $this->setIncludedInEncode(false, EncodingContext::MODE_STORE)
            ->setIncludedInDecode(false, DecodingContext::MODE_LOAD);
    }

    /**
     * Is this field a proxy to an owner field?
     */
    public function isProxyForOwnerField(): bool
    {
        return isset($this->proxyForOwnerField);
    }

    /**
     * Returns the name of the owner field this field proxies (if any).
     */
    public function getProxyForOwnerField(): ?string
    {
        return $this->proxyForOwnerField;
    }

    /**
     * Makes this field a proxy to the owner field with the given name.
     *
     * NOTE: This only works for subclasses of `Entity`.
     *
     * @param string|null $name Name of the owner field, defaults to the same name as this field.
     *
     * @return $this
     */
    public function setProxyForOwnerField(?string $name = null): self
    {
        $this->proxyForOwnerField = $name ?? $this->getName();
        return $this->setExternal();
    }

    /**
     * Field cannot be updated based on external input.
     *
     * NOTE: programmatic assignment is still possible!
     *
     * @return $this
     */
    public function setReadOnly(): self
    {
        return
            $this->setIncludedInDecode(false, DecodingContext::MODE_INPUT)
            ->setIncludedInValidate(false);
    }

    /**
     * Exclude from encode/decode/validation.
     *
     * @return $this
     */
    public function setExcluded(): self
    {
        return
            $this->setIncludedInEncode(false)
            ->setIncludedInDecode(false)
            ->setIncludedInValidate(false);
    }

    /**
     * Validation rules container.
     *
     * Can be used to register custom validation rules.
     */
    public function getValidationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    /**
     * Field is included in validation?
     */
    public function isIncludedInValidate(): bool
    {
        return $this->validationRules->isEnabled();
    }

    /**
     * Enable/disable validation for this field.
     *
     * @return $this
     */
    public function setIncludedInValidate(bool $include): self
    {
        $this->validationRules->setEnabled($include);
        return $this;
    }

    /**
     * Modify validation rules.
     *
     * @param Closure $closure Closure is called with the validation rules as argument.
     *
     * @return $this
     */
    public function modifyValidationRules(Closure $closure): self
    {
        $closure($this->validationRules);
        return $this;
    }

    /**
     * Property annotation for this field.
     */
    public function getAnnotation(): string
    {
        $nullable = $this->allowsNull() ? '?' : '';
        return "@property {$nullable}{$this->getType()->getAnnotationType()} \${$this->getName()}";
    }

    /**
     * TypeScript annotation for this field.
     */
    public function getTypeScriptAnnotation(): string
    {
        $optional = $this->allowsNull() ? '?' : '';
        $nullable = $this->allowsNull() ? ' | null' : '';
        return "{$this->getName()}{$optional}: {$this->getType()->getTypeScriptAnnotationType()}{$nullable}";
    }

    /**
     * Describe this field type as JSON Schema.
     *
     * @return array
     */
    public function toJSONSchema(Context $context): array
    {
        $base = [];

        if (!empty($this->getDocumentation()->getShortDescription())) {
            $base['description'] = $this->getDocumentation()->getShortDescription();
        }

        $purposeDetails = $this->getPurposeSpecification()->getAllPurposeDetails();
        if (count($purposeDetails) > 0) {
            $base['purposeSpecification']['purposes'] = [];
            foreach ($purposeDetails as $purposeDetail) {
                $base['purposeSpecification']['purposes'][$purposeDetail->purpose->getIdentifier()] = array_filter([
                    'description' => $purposeDetail->purpose->getLabel(),
                    'subPurpose' => [
                        'identifier' => $purposeDetail->subPurpose->getIdentifier(),
                        'description' => $purposeDetail->subPurpose->getLabel(),
                    ],
                ]);
            }
        }

        $remark = $this->getPurposeSpecification()->remark;
        if (!is_null($remark)) {
            $base['purposeSpecification']['remark'] = $remark;
        }

        $type = $this->type->toJSONSchema($context);

        return array_merge($base, $type);
    }

    /**
     * Helper method for creating a new instance of the appropriate version for a schema based field.
     *
     * WARNING: this method will throw a RuntimeException when called on non-schema based fields!
     */
    public function newInstance(): SchemaObject
    {
        $type = $this->getType();
        if (!($type instanceof SchemaType)) {
            throw new RuntimeException(__METHOD__ . ' called on non-schema type!');
        }

        return $type->getSchemaVersion()->newInstance();
    }

    /**
     * Converts the commonly exported fields to an array, so they can be exported to csv.
     */
    public function toExportArray(): array
    {
        return [
            $this->getSchema()->getDocumentationIdentifier(),
            $this->getName(),
            $this->getDocumentation()->getLabel(),
            $this->getDocumentation()->getShortDescription(),
            $this->getType()->getAnnotationType(),
            $this->getPurposeSpecification()->remark,
            $this->getPurposeSpecification()->getPurposeDetail(Purpose::EpidemiologicalSurveillance)?->subPurpose->getLabel(),
            $this->getPurposeSpecification()->getPurposeDetail(Purpose::QualityOfCare)?->subPurpose->getLabel(),
            $this->getPurposeSpecification()->getPurposeDetail(Purpose::AdministrativeAdvice)?->subPurpose->getLabel(),
            $this->getPurposeSpecification()->getPurposeDetail(Purpose::OperationalAdjustment)?->subPurpose->getLabel(),
            $this->getPurposeSpecification()->getPurposeDetail(Purpose::ScientificResearch)?->subPurpose->getLabel(),
            $this->getPurposeSpecification()->getPurposeDetail(Purpose::ToBeDetermined)?->subPurpose->getLabel(),
        ];
    }
}

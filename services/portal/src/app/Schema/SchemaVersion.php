<?php

declare(strict_types=1);

namespace App\Schema;

use App\Schema\Conditions\ConditionHelper;
use App\Schema\Documentation\Traits\HasDocumentation;
use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Purpose\PurposeLimited;
use App\Schema\Test\Factory;
use App\Schema\Types\SchemaType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationResult;
use App\Schema\Validation\ValidationRules;
use App\Schema\Validation\Validator;
use Closure;
use Generator;
use InvalidArgumentException;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use RuntimeException;
use stdClass;
use Throwable;
use Webmozart\Assert\Assert;

use function array_filter;
use function array_merge;
use function assert;
use function call_user_func;
use function is_a;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * @template T of SchemaObject
 */
class SchemaVersion
{
    use HasDocumentation;

    /** @var Schema<T> */
    private Schema $schema;

    private int $version;

    /** @var array<Field> */
    private array $fields;

    private ValidationRules $validationRules;

    /**
     * @param Schema<T> $schema
     */
    public function __construct(Schema $schema, int $version)
    {
        $this->schema = $schema;
        $this->version = $version;

        $fields = array_filter($this->schema->getFields(), fn(Field $field) => $field->isInVersion($this->version));

        $this->fields = [];
        $this->validationRules = new ValidationRules();
        foreach ($fields as $field) {
            $this->fields[$field->getName()] = $field;
            $this->validationRules->addChild($field->getValidationRules(), $field->getName());
        }
    }

    /**
     * Returns the schema.
     *
     * @return Schema<T>
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Returns the version number.
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    protected function getDefaultDocumentationIdentifier(): string
    {
        return $this->getSchema()->getDocumentationIdentifier();
    }

    /**
     * @inheritdoc
     */
    protected function getDocumentationIdentifiers(): array
    {
        $identifiers = [];
        foreach ($this->getSchema()->getDocumentation()->getIdentifiers() as $identifier) {
            $identifiers[] = $identifier . '.v' . $this->getVersion();
        }

        return array_merge($identifiers, $this->getSchema()->getDocumentation()->getIdentifiers());
    }

    /**
     * Returns the fields for this schema version indexed by name.
     *
     * @return array<Field>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Returns the field with the given name.
     *
     * If the field doesn't exist, null will be returned.
     */
    public function getField(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * Returns the field with the given name.
     *
     * If the field doesn't exist, an InvalidArgumentException will be thrown.
     */
    public function getExpectedField(string $name): Field
    {
        if (!isset($this->fields[$name])) {
            throw new InvalidArgumentException(
                "Expected field \"{$name}\" does not exists for version " . $this->getVersion() . "!",
            );
        }

        return $this->fields[$name];
    }

    /**
     * Returns the validation rules container for this schema version.
     */
    public function getValidationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    /**
     * Validate data against this schema version.
     *
     * @param array $data
     */
    public function validate(array $data, ?ValidationContext $context = null): ValidationResult
    {
        $validator = new Validator($context, $this->getValidationRules());
        return $validator->validate($data);
    }

    /**
     * Yields the sorted field based on the registered conditions in the condition helper.
     *
     * @param Closure $filter Filter the fields that should be yielded.
     * @param Closure $getCondition Returns a condition for evaluation for the field.
     * @param SchemaObject $object Object to extract data from for evaluation purposes.
     *
     * @return Generator<Field, bool>
     *
     * @throws CodableException
     */
    private function getFilteredAndSortedFields(Closure $filter, Closure $getCondition, SchemaObject $object): Generator
    {
        $conditionHelper = new ConditionHelper();

        if ($this->getSchema()->getOwnerFieldName() !== null) {
            $conditionHelper->add($this->getSchema()->getOwnerFieldName());
        }

        foreach ($this->getFields() as $field) {
            $conditionHelper->add(
                $field->getName(),
                $getCondition($field),
            );
        }

        try {
            $fieldNames = $conditionHelper->getSortedFields();
        } catch (Throwable $exception) {
            throw new CodableException($exception->getMessage(), $exception->getCode(), $exception);
        }

        foreach ($fieldNames as $fieldName) {
            if ($fieldName === $this->getSchema()->getOwnerFieldName()) {
                continue;
            }

            $field = $this->getExpectedField($fieldName);

            if (!$filter($field)) {
                continue;
            }

            yield $field => $conditionHelper->evaluate($fieldName, $object);
        }
    }

    private function isFieldIncludedInEncode(Field $field, EncodingContext $context): bool
    {
        if ($context->getView() !== null && !$field->isInView($context->getView())) {
            return false;
        }

        if (!$field->isIncludedInEncode($context->getMode())) {
            return false;
        }

        if (!$context instanceof PurposeLimited) {
            return true;
        }

        return $context->getPurposeLimitation()->isIncluded($field->getPurposeSpecification());
    }

    /**
     * Encode fields.
     *
     * @param T $source
     *
     * @throws CodableException
     */
    public function encode(EncodingContainer $container, SchemaObject $source): void
    {
        $sortedFields = $this->getFilteredAndSortedFields(
            fn (Field $f) => $this->isFieldIncludedInEncode($f, $container->getContext()),
            static fn (Field $f) => $f->getEncodingCondition($container->getContext()->getMode()),
            $source,
        );

        $container->encodeObject(new stdClass());

        foreach ($sortedFields as $field => $result) {
            if ($result) {
                $field->encode($container, $source);
            }
        }
    }

    private function isFieldIncludedInDecode(Field $field, DecodingContext $context): bool
    {
        if ($context->getView() !== null && !$field->isInView($context->getView())) {
            return false;
        }

        return $field->isIncludedInDecode($context->getMode());
    }

    /**
     * @param DecodingContainer $container Decoding container.
     * @param T|null $target Optional target object to decode into.
     *
     * @return T|null
     *
     * @throws CodableException
     */
    public function decode(DecodingContainer $container, ?SchemaObject $target = null): ?SchemaObject
    {
        if (!$container->isPresent()) {
            return null;
        }

        $target ??= $this->newInstance();

        $sortedFields = $this->getFilteredAndSortedFields(
            fn (Field $f) => $this->isFieldIncludedInDecode($f, $container->getContext()),
            static fn (Field $f) => $f->getDecodingCondition($container->getContext()->getMode()),
            $target,
        );

        foreach ($sortedFields as $field => $result) {
            if ($result) {
                $field->decode($container, $target);
            } else {
                $target->{$field->getName()} = null;
            }
        }

        return $target;
    }

    /**
     * Creates a new uninitialized instance for this schema verison.
     *
     * @return T Instance for this schema version.
     */
    public function newUninitializedInstance()
    {
        $factory = $this->getSchema()->getObjectFactory();

        if (is_string($factory) && is_a($factory, SchemaObjectFactory::class, true)) {
            /** @var T $instance */
            $instance = $factory::newUninitializedInstanceWithSchemaVersion($this);
            return $instance;
        }

        if (is_callable($factory)) {
            /** @var T $obj */
            $obj = call_user_func($factory, $this);
            return $obj;
        }

        $class = $this->getClass();
        if (is_a($class, SchemaObjectFactory::class, true)) {
            /** @var T $instance */
            $instance = $class::newUninitializedInstanceWithSchemaVersion($this);
            return $instance;
        }

        throw new RuntimeException(
            'Schema class ' . $this->getSchema()->getClass() . ' does not implement the SchemaObjectFactory interface and no explicit object factory set!',
        );
    }

    /**
     * Create an object instance for this schema version.
     *
     * @return T Instance for this schema version.
     */
    public function newInstance(): SchemaObject
    {
        $instance = $this->newUninitializedInstance();

        foreach ($this->getFields() as $field) {
            $field->init($instance);
        }

        return $instance;
    }

    /**
     * Creates an encodable decorator for this schema version.
     */
    public function getEncodableDecorator(): EncodableDecorator
    {
        return new class ($this) implements EncodableDecorator {
            private SchemaVersion $schemaVersion;

            public function __construct(SchemaVersion $schemaVersion)
            {
                $this->schemaVersion = $schemaVersion;
            }

            public function encode(object $value, EncodingContainer $container): void
            {
                assert($value instanceof SchemaObject);
                $this->schemaVersion->encode($container, $value);
            }
        };
    }

    /**
     * Creates a decodable decorator for this schema version.
     */
    public function getDecodableDecorator(): DecodableDecorator
    {
        return new class ($this) implements DecodableDecorator {
            private SchemaVersion $schemaVersion;

            public function __construct(SchemaVersion $schemaVersion)
            {
                $this->schemaVersion = $schemaVersion;
            }

            public function decode(string $class, DecodingContainer $container, ?object $object = null): object
            {
                if (
                    $object !== null
                    && (
                        !$object instanceof SchemaObject
                        || (string) $object->getSchemaVersion() !== (string) $this->schemaVersion
                    )
                ) {
                    throw new CodableException('Target object is not compatible with this schema version!');
                }

                $result = $this->schemaVersion->decode($container, $object);
                if ($result === null) {
                    throw new CodableException('Cannot decode object from an empty container!');
                }

                return $result;
            }
        };
    }

    /**
     * Returns the class for object instances based on this schema version.
     *
     * @return class-string<T>
     */
    public function getClass(): string
    {
        if ($this->getSchema()->isUsingVersionedClasses()) {
            $namespace = $this->getSchema()->getVersionedNamespace();
            $class = sprintf('%s\%sV%d', $namespace, $this->getSchema()->getName(), $this->getVersion());
        } else {
            $class = $this->getSchema()->getClass();
        }

        Assert::classExists($class);

        return $class;
    }

    /**
     * Diff with the given schema version.
     */
    public function diff(SchemaVersion $other): SchemaDiff
    {
        return new SchemaDiff($this, $other);
    }

    /**
     * Check if 2 schema versions are equal.
     */
    public function isEqual(SchemaVersion $other): bool
    {
        if ($other === $this) {
            return true;
        }

        return $other->getClass() === $this->getClass() && $other->getVersion() === $this->getVersion();
    }

    /**
     * Returns the test factory for this schema version.
     */
    public function getTestFactory(): Factory
    {
        return $this->getSchema()->getTestFactory()->schemaVersion($this);
    }

    /**
     * String representation.
     */
    public function __toString(): string
    {
        return $this->getSchema() . '#' . $this->getVersion();
    }

    /**
     * Create field for this schema version.
     */
    public function createField(string $name): Field
    {
        return new Field($name, new SchemaType($this));
    }

    /**
     * Create array field for this schema version.
     */
    public function createArrayField(string $name): Field
    {
        return new ArrayField($name, new SchemaType($this));
    }
}

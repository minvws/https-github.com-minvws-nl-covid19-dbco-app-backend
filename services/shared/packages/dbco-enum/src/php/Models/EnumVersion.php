<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Types\EnumVersionType;
use InvalidArgumentException;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodingContainer;

/**
 * Gives access to enum values filtered for a specific version.
 *
 * @template T of Enum
 */
class EnumVersion
{
    /**
     * @var class-string<T>
     */
    private string $enumClass;

    /**
     * @var int
     */
    private int $version;

    /**
     * @var array<string|int, T>|null
     */
    private ?array $all = null;

    /**
     * Constructor.
     *
     * @param class-string<T> $enumClass
     * @param int             $version
     */
    public function __construct(string $enumClass, int $version)
    {
        $this->enumClass = $enumClass;
        $this->version = $version;
    }

    /**
     * Load values for this version.
     *
     * @return array<string|int, T>
     */
    protected function load(): array
    {
        if ($this->all === null) {
            $class = $this->getEnumClass();
            /** @var T[] $allOptions */
            $allOptions = $class::all();
            $options = array_filter($allOptions, fn(Enum $v) => $v->isInVersion($this->getVersion()));
            $this->all = array_combine(array_map(fn(Enum $o) => $o->value, $options), $options) ?: [];
        }

        return $this->all;
    }

    /**
     * Enum class.
     *
     * @return class-string<T>
     */
    final public function getEnumClass(): string
    {
        return $this->enumClass;
    }

    /**
     * Returns the enum schema.
     *
     * TODO: Should be a more formal object.
     *
     * @return object
     */
    final public function getSchema(): object
    {
        $class = $this->getEnumClass();
        return $class::getSchema();
    }

    /**
     * Enum version.
     *
     * @return int
     */
    final public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Returns all enum items.
     *
     * return T[]
     */
    final public function all(): array
    {
        return array_values($this->load());
    }

    /**
     * Returns all valid values.
     *
     * @return string[]|int[]
     */
    final public function allValues(): array
    {
        return array_keys($this->load());
    }

    /**
     * Returns the enum item for the given value
     *
     * @throws InvalidArgumentException If the value is not a valid value.
     */
    final public function from(string|int $value): ?Enum
    {
        $this->load();

        if (!array_key_exists($value, $this->all ?? [])) {
            throw new InvalidArgumentException(sprintf('Invalid value "%s" for "%s"', $value, $this));
        }

        return $this->all[$value] ?? null;
    }

    /**
     * Returns the enum item for the given value or null
     */
    final public function tryFrom(string|int $value): ?Enum
    {
        try {
            return $this->from($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Returns the enum items for the given value.
     *
     * @return T[] Enum items for the given values.
     *
     * @throws InvalidArgumentException If the array contains an invalid value.
     */
    final public function forValues(array $values): array
    {
        return array_filter(
            array_map(fn ($v) => $this->from($v), $values),
            fn ($o) => $o !== null
        );
    }

    /**
     * Decode value.
     *
     * @param DecodingContainer $container
     *
     * @return T|null
     *
     * @throws CodableException
     */
    public function decode(DecodingContainer $container): ?Enum
    {
        $value = $container->decodeStringIfPresent();

        if ($value === null) {
            return null;
        }

        try {
            return $this->from($value);
        } catch (InvalidArgumentException) {
            throw new CodableException(sprintf('Unexpected value: %s', $value));
        }
    }

    /**
     * Returns the enum value with the given name.
     *
     * @param string $name Enum value name.
     * @param array  $args Arguments (ignored).
     *
     * @return T
     *
     * @throws InvalidArgumentException If the name is not a valid name.
     */
    public function __call(string $name, array $args): Enum
    {
        $class = $this->getEnumClass();
        $option = $class::__callStatic($name, $args);
        if ($option->isInVersion($this->getVersion())) {
            return $option;
        } else {
            throw new InvalidArgumentException("Invalid option name \"{$name}\" for \"{$this}\"");
        }
    }

    /**
     * String representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->enumClass . '#' . $this->version;
    }

    /**
     * Create field for this enum version.
     *
     * @param string $name
     *
     * @return Field
     */
    public function createField(string $name): Field
    {
        return new Field($name, new EnumVersionType($this));
    }

    /**
     * Create array field for this enum version.
     *
     * @param string $name
     *
     * @return ArrayField
     */
    public function createArrayField(string $name): ArrayField
    {
        return new ArrayField($name, new EnumVersionType($this));
    }
}

<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use JsonSerializable;
use MinVWS\Codable\Codable;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;
use stdClass;
use Stringable;

use function array_filter;
use function array_key_exists;
use function array_map;
use function property_exists;

/**
 * Base class for enums.
 *
 * @property-read string|int $value
 * @property-read string $label
 * @property-read array $children
 * @property-read int $minVersion
 * @property-read ?int $maxVersion
 */
abstract class Enum implements Codable, Castable, JsonSerializable, Stringable
{
    private static array $byValue = [];
    private static array $byName = [];

    private static array $currentVersionByClass = [];
    private static array $maxVersionByClass = [];

    /**
     * @var Enum|null
     */
    private ?Enum $_parent = null;

    /**
     * @var static[]
     */
    private array $_children = [];

    /**
     * @var string|int|null
     */
    private $_value;

    /**
     * @var string
     */
    private string $_label;

    /**
     * @var array
     */
    private array $_properties;

    /**
     * @var int
     */
    private int $_minVersion;

    /**
     * @var int|null
     */
    private ?int $_maxVersion;

    /**
     * Returns the enum schema.
     *
     * @return object
     */
    abstract protected static function enumSchema(): object;

    /**
     * Returns this enum's schema definition.
     *
     * TODO: Should be a more formal object.
     *
     * @return object
     */
    final public static function getSchema(): object
    {
        return static::enumSchema();
    }

    /**
     * Returns the minimum enum version.
     *
     * @return EnumVersion<static>
     */
    final public static function getMinVersion(): EnumVersion
    {
        static::init();
        return static::getVersion(1);
    }

    /**
     * Returns the maximum enum version.
     *
     * @return EnumVersion<static>
     */
    final public static function getMaxVersion(): EnumVersion
    {
        static::init();
        return static::getVersion(static::$maxVersionByClass[static::class]);
    }

    /**
     * Returns the current enum version.
     *
     * @return EnumVersion
     */
    final public static function getCurrentVersion(): EnumVersion
    {
        static::init();
        return static::getVersion(static::$currentVersionByClass[static::class] ?? static::$maxVersionByClass[static::class]);
    }

    /**
     * Returns the given enum version.
     *
     * @param int $version
     *
     * @return EnumVersion<static>
     */
    final public static function getVersion(int $version): EnumVersion
    {
        static::init();

        if ($version > static::$maxVersionByClass[static::class]) {
            throw new InvalidArgumentException('Invalid version ' . $version . ' for ' . static::class);
        }

        return new EnumVersion(static::class, $version);
    }

    /**
     * We keep a static cache with all possible items so comparisons
     * can be made using instances instead having to compare the value.
     */
    private static function init(): void
    {
        if (isset(static::$byValue[static::class])) {
            return;
        }

        static::$byValue[static::class] = [];
        static::$byName[static::class] = [];

        static::$currentVersionByClass[static::class] = static::enumSchema()->currentVersion ?? null;
        static::$maxVersionByClass[static::class] = 1;

        $rawItems = static::enumSchema()->items;
        static::registerItems($rawItems);
    }

    /**
     * Create enum item for the given raw item.
     *
     * @param object $rawItem
     *
     * @return static
     */
    private static function createItem(object $rawItem): Enum
    {
        $value = $rawItem->value ?? null; // can be null for items that can't be selected but might act as a parent
        $label = $rawItem->label;
        $minVersion = $rawItem->minVersion ?? 1;
        $maxVersion = $rawItem->maxVersion ?? null;

        static::$maxVersionByClass[static::class] = max(static::$maxVersionByClass[static::class], $minVersion, $maxVersion !== null ? $maxVersion + 1 : $minVersion);

        $properties = [];
        foreach (get_object_vars(static::enumSchema()->properties ?? new stdClass()) as $propertyName => $propertyData) {
            if (!in_array($propertyData->scope ?? 'shared', ['php', 'shared'])) {
                continue;
            }

            $propertyValue = $rawItem->$propertyName ?? null;
            $propertyEnumClass = ($propertyData->namespace ?? __NAMESPACE__) . '\\' . $propertyData->phpType;
            if (class_exists($propertyEnumClass) && $propertyValue !== null) {
                $propertyValue = $propertyEnumClass::from($propertyValue);
            }
            $properties[$propertyName] = $propertyValue;
        }

        return new static($value, $label, $properties, $minVersion, $maxVersion);
    }

    /**
     * Register enum items for the given raw items.
     *
     * Optionally adding them as children for the given parent.
     *
     * @param array     $rawItems
     * @param Enum|null $parent
     */
    private static function registerItems(array $rawItems, ?Enum $parent = null): void
    {
        foreach ($rawItems as $rawItem) {
            $item = static::createItem($rawItem);

            if ($parent !== null) {
                $item->_parent = $parent;
                $parent->children[] = $item;
            }

            if (isset($rawItem->items)) {
                static::registerItems($rawItem->items, $item);
            }

            if (isset($rawItem->value)) {
                static::$byValue[static::class][$rawItem->value] = $item;
            }

            $name = $rawItem->name ?? $rawItem->value ?? null;
            if (isset($name)) {
                static::$byName[static::class][$name] = $item;
            }
        }
    }

    /**
     * Returns all enum items.
     *
     * return static[]
     */
    public static function all(): array
    {
        static::init();
        return array_values(static::$byValue[static::class]);
    }

    /**
     * Returns all valid values.
     */
    public static function allValues(): array
    {
        static::init();

        return array_keys(static::$byValue[static::class]);
    }

    public static function allValuesForProperty(string $property): array
    {
        static::init();

        return collect(static::all())->map(fn($enum) => $enum->$property)->toArray();
    }

    /**
     * Returns the default item for this enum (if any).
     */
    public static function defaultItem(): ?static
    {
        $defaultValue = static::enumSchema()->default ?? null;

        if ($defaultValue === null) {
            return null;
        }

        return static::tryFrom($defaultValue);
    }

    /**
     * Returns the tsConst name from the schema (if any).
     *
     * Can be null.
     *
     * @return string|null
     */
    public static function tsConst(): ?string
    {
        return static::enumSchema()->tsConst ?? null;
    }

    /**
     * Returns the enum item for the given value.
     *
     * @throws InvalidArgumentException If the value is not a valid value.
     */
    final public static function from(string|int $value): static
    {
        static::init();

        if (!array_key_exists($value, static::$byValue[static::class])) {
            throw new InvalidArgumentException('Invalid value "' . $value . '"');
        }

        return static::$byValue[static::class][$value];
    }

    final public static function fromOptional(string|int|null $value): ?static
    {
        if ($value === null) {
            return null;
        }

        return self::from($value);
    }

    /**
     * Returns the enum item for the given value or null
     */
    final public static function tryFrom(string|int $value): ?static
    {
        try {
            return self::from($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    final public static function tryFromOptional(string|int|null $value): ?static
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * @param int|string $value
     */
    final public static function forValueByProperty($value, string $property): ?self
    {
        static::init();

        return collect(static::all())->first(function (Enum $enum) use ($value, $property) {
            return $enum->$property === $value;
        });
    }

    /**
     * Returns the enum items for the given value.
     *
     * @param string[] $values
     *
     * @return static[] Enum items for the given values.
     *
     * @throws InvalidArgumentException If the array contains an invalid value.
     */
    final public static function fromArray(array $values): array
    {
        return array_filter(
            array_map(fn ($v) => static::from($v), $values),
            fn ($o) => $o !== null
        );
    }

    /**
     * Returns the enum items for the given value.
     *
     * @param string[] $values
     *
     * @return static[] Enum items for the given values.
     *
     * @throws InvalidArgumentException If the array contains an invalid value.
     */
    final public static function tryFromArray(array $values): array
    {
        return array_filter(
            array_map(fn ($v) => static::tryFrom($v), $values),
            fn ($o) => $o !== null
        );
    }

    /**
     * Constructor.
     *
     * @param string|int|null $value      Item value. Value is null for items that can't be selected.
     * @param string          $label      Label.
     * @param array           $properties Extra properties.
     * @param int             $minVersion Minimum supported enum version.
     * @param ?int            $maxVersion Maximum supported enum version.
     */
    final private function __construct($value, string $label, array $properties, int $minVersion, ?int $maxVersion)
    {
        $this->_value = $value;
        $this->_label = $label;
        $this->_properties = $properties;
        $this->_minVersion = $minVersion;
        $this->_maxVersion = $maxVersion;
    }

    /**
     * Returns if this enum value exists for the given value.
     *
     * @param int $version
     *
     * @return bool
     */
    public function isInVersion(int $version): bool
    {
        return $version >= $this->_minVersion && ($this->_maxVersion === null || $version <= $this->_maxVersion);
    }

    /**
     * Return if the given property is set.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return
            in_array($name, ['value', 'label', 'parent', 'children', 'minVersion', 'maxVersion']) ||
            isset($this->_properties[$name]) ||
            (array_key_exists($name, static::enumSchema()->traitProperties ?? []) && isset($this->$name));
    }

    /**
     * Returns the value for the given property or null if it doesn't exist.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (in_array($name, ['value', 'label', 'parent', 'children', 'minVersion', 'maxVersion'])) {
            return $this->{"_$name"};
        } elseif (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        } elseif (property_exists(static::enumSchema(), 'traitProperties') && array_key_exists($name, json_decode(json_encode(static::enumSchema()->traitProperties), true) ?? [])) {
            $propertyData = static::enumSchema()->traitProperties->$name;
            /** @phpstan-ignore-next-line */
            return call_user_func([$this, $propertyData->method]);
        } else {
            $trace = debug_backtrace();
            throw new \ErrorException(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line']
            );
        }
    }

    /**
     * Returns the enum value with the given name.
     *
     * @param string $name Enum value name.
     * @param array  $args Arguments (ignored).
     *
     * @return static
     *
     * @throws InvalidArgumentException If the name is not a valid name.
     */
    public static function __callStatic(string $name, array $args): Enum
    {
        static::init();

        if (!array_Key_exists($name, static::$byName[static::class])) {
            throw new InvalidArgumentException('Invalid name "' . $name . '" for "' . static::class . '"');
        }

        return static::$byName[static::class][$name];
    }

    /**
     * Encode value.
     *
     * @param EncodingContainer $container
     */
    public function encode(EncodingContainer $container): void
    {
        if (property_exists(static::enumSchema(), 'scalarType')) {
            $scalarType = static::enumSchema()->scalarType;
        } else {
            $scalarType = 'string';
        }

        switch ($scalarType) {
            case 'string':
                $container->encodeString((string) $this->_value);
                break;
            case 'int':
                $container->encodeInt((int) $this->_value);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Invalid scalarType given "%s"', $scalarType));
        }
    }

    /**
     * Decode value.
     *
     * @param DecodingContainer $container
     * @param object|null       $object
     *
     * @return static|null
     *
     * @throws CodableException
     */
    public static function decode(DecodingContainer $container, ?object $object = null)
    {
        $value = $container->decodeStringIfPresent();

        if ($value === null) {
            return null;
        }

        try {
            return static::from($value);
        } catch (InvalidArgumentException $e) {
            throw new CodableException("Unexpected value: " . $value);
        }
    }

    /**
     * Get the caster class to use when casting from / to this cast target.
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class (static::class) implements CastsAttributes {
            private string $class;

            public function __construct(string $class)
            {
                $this->class = $class;
            }

            public function get($model, $key, $value, $attributes)
            {
                if ($value === null) {
                    return null;
                }

                return call_user_func([$this->class, 'from'], $value);
            }

            public function set($model, $key, $value, $attributes)
            {
                return $value->value ?? null;
            }
        };
    }

    /**
     * Serialize to JSON.
     *
     * @return mixed|string|null
     */
    public function jsonSerialize(): mixed
    {
        return $this->_value;
    }

    /**
     * String representation of value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->_value;
    }
}

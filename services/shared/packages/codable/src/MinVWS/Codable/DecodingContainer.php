<?php

namespace MinVWS\Codable;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * Decoding container.
 *
 * @package MinVWS\Codable
 */
class DecodingContainer
{
    // NOTE: class members have a _ prefix to prevent clashes with
    //       the magic getter.

    /**
     * @var DecodingContext
     */
    private DecodingContext $_context;

    /**
     * @var mixed
     */
    private $_value;

    /**
     * @var DecodingContainer|null
     */
    private ?DecodingContainer $_parent;

    /**
     * @var string|int|null
     */
    private $_key;

    /**
     * Constructor.
     *
     * @param mixed                  $value
     * @param DecodingContext        $context
     * @param DecodingContainer|null $parent
     * @param string|int|null        $key
     */
    public function __construct($value, DecodingContext $context, ?DecodingContainer $parent = null, $key = null)
    {
        $this->_value = $value;
        $this->_context = $context;
        $this->_parent = $parent;
        $this->_key = $key;
    }

    /**
     * Returns the value.
     *
     * @return mixed
     */
    private function getValue()
    {
        return $this->_value;
    }

    /**
     * Is data present?
     *
     * @return bool
     */
    public function isPresent(): bool
    {
        return $this->getValue() !== null;
    }

    /**
     * Context.
     *
     * @return DecodingContext
     */
    public function getContext(): DecodingContext
    {
        return $this->_context;
    }

    public function getRoot(): DecodingContainer
    {
        if ($this->getParent() !== null) {
            return $this->getParent()->getRoot();
        }

        return $this;
    }

    /**
     * Returns the parent container.
     *
     * @return DecodingContainer|null
     */
    public function getParent(): ?DecodingContainer
    {
        return $this->_parent;
    }

    /**
     * Get key.
     *
     * @return string|int|null
     */
    public function getKey()
    {
        return $this->_key;
    }


    /**
     * Decode using the given type or detected type.
     *
     * @param string|null $type PHP type.
     *
     * @return string|int
     *
     * @throws KeyNotFoundException|KeyTypeMismatchException|CodablePathException
     */
    public function decodeKey(?string $type = null)
    {
        if ($this->getValue() === null) {
            throw new KeyNotFoundException($this->getPath());
        }

        $type = $type ?: gettype($this->_value);

        switch ($type) {
            case 'string':
                return $this->decodeStringKey();
            case 'int':
            case 'integer':
            case 'long':
                return $this->decodeIntKey();
            default:
                // not possible
                throw new CodablePathException($this->getPath(), 'Unsupported key type');
        }
    }

    /**
     * Decode key if present using the given type or detected type.
     *
     * @param string|null $type PHP type.
     *
     * @return string|int|null
     *
     * @throws KeyTypeMismatchException|KeyNotFoundException|CodablePathException
     */
    public function decodeKeyIfPresent(?string $type = null)
    {
        if ($this->getKey() !== null) {
            return $this->decodeKey($type);
        } else {
            return null;
        }
    }

    /**
     * Decode string key.
     *
     * @throws KeyNotFoundException
     * @throws KeyTypeMismatchException
     */
    public function decodeStringKey(): string
    {
        if ($this->getKey() === null) {
            throw new KeyNotFoundException($this->getPath());
        } elseif (!is_string($this->getKey())) {
            throw new KeyTypeMismatchException($this->getPath(), gettype($this->getKey()), 'string');
        } else {
            return $this->getKey();
        }
    }

    /**
     * Decode string key if present.
     *
     * @throws KeyTypeMismatchException
     * @throws KeyNotFoundException
     */
    public function decodeStringKeyIfPresent(): ?string
    {
        if ($this->getKey() === null) {
            return null;
        } else {
            return $this->decodeStringKey();
        }
    }

    /**
     * Decode int key.
     *
     * @throws KeyNotFoundException
     * @throws KeyTypeMismatchException
     */
    public function decodeIntKey(): int
    {
        if ($this->getKey() === null) {
            throw new KeyNotFoundException($this->getPath());
        } elseif (!is_int($this->getKey())) {
            throw new KeyTypeMismatchException($this->getPath(), gettype($this->getKey()), 'int');
        } else {
            return $this->getKey();
        }
    }

    /**
     * Decode int key if present.
     *
     * @throws KeyTypeMismatchException
     * @throws KeyNotFoundException
     */
    public function decodeIntKeyIfPresent(): ?int
    {
        if ($this->getKey() === null) {
            return null;
        } else {
            return $this->decodeIntKey();
        }
    }

    /**
     * Get path.
     *
     * @return array
     */
    public function getPath(): array
    {
        if ($this->getParent() !== null && $this->getKey() !== null) {
            return array_merge($this->getParent()->getPath(), [$this->getKey()]);
        } else {
            return [];
        }
    }

    /**
     * Decode using the given type or detected type.
     *
     * @param string|null $type PHP type or full class name.
     * @param string|null $elementType In case of an array; element PHP type or full class name.
     * @param mixed|null  $current Current value (only supported for objects).
     *
     * @return mixed
     *
     * @throws ValueNotFoundException
     * @throws ValueTypeMismatchException
     */
    public function decode(?string $type = null, ?string $elementType = null, $current = null)
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        }

        $type = $type ?: gettype($this->_value);

        switch ($type) {
            case 'string':
                return $this->decodeString();
            case 'int':
            case 'integer':
            case 'long':
                return $this->decodeInt();
            case 'float':
            case 'double':
                return $this->decodeFloat();
            case 'bool':
            case 'boolean':
                return $this->decodeBool();
            case 'array':
                return $this->decodeArray($elementType);
            default:
                return $this->decodeObject($type, $current);
        }
    }

    /**
     * Decode if present using the given type or detected type.
     *
     * @param string|null $type
     * @param string|null $elementType
     * @param mixed|null  $current
     *
     * @return mixed
     *
     * @throws ValueNotFoundException
     * @throws ValueTypeMismatchException
     */
    public function decodeIfPresent(?string $type = null, ?string $elementType = null, $current = null)
    {
        if ($this->isPresent()) {
            return $this->decode($type, $elementType, $current);
        } else {
            return null;
        }
    }


    /**
     * Decode string.
     *
     * @return string
     *
     * @throws ValueNotFoundException
     * @throws ValueTypeMismatchException
     */
    public function decodeString(): string
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } elseif (!is_string($this->getValue())) {
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), 'string');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode string if present.
     *
     * @return string|null
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeStringIfPresent(): ?string
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeString();
        }
    }

    /**
     * Decode int.
     *
     * @return int
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeInt(): int
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } elseif (!is_int($this->getValue())) {
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), 'int');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode int if present.
     *
     * @return int|null
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeIntIfPresent(): ?int
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeInt();
        }
    }

    /**
     * Decode float.
     *
     * @return float
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeFloat(): float
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } elseif (!is_float($this->getValue())) {
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), 'float');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode float if present.
     *
     * @return float|null
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeFloatIfPresent(): ?float
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeFloat();
        }
    }

    /**
     * Decode bool.
     *
     * @return bool
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeBool(): bool
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } elseif (!is_bool($this->getValue())) {
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), 'bool');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode bool if present.
     *
     * @return bool|null
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeBoolIfPresent(): ?bool
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeBool();
        }
    }

    /**
     * Decode date time using the given format.
     *
     * @template T
     *
     * @param string|null       $format
     * @param DateTimeZone|null $tz
     * @param class-string<T>   $class
     *
     * @return T
     *
     * @throws DateTimeFormatException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeDateTime(string $format = null, DateTimeZone $tz = null, $class = DateTime::class): DateTimeInterface
    {
        $string = $this->decodeStringIfPresent();

        if ($class === DateTimeInterface::class) {
            $class = DateTime::class;
        }

        if ($format === null) {
            try {
                $dateTime = new $class($string, $tz);
            } catch (Exception $e) {
                throw new DateTimeFormatException($this->getPath(), '<any>');
            }
        } else {
            $dateTime = $class::createFromFormat(\sprintf('!%s', $format), $string, $tz);
            if (!$dateTime) {
                throw new DateTimeFormatException($this->getPath(), $format);
            }
        }

        return $dateTime;
    }

    /**
     * Decode date time using the given format if present.
     *
     * @template T
     *
     * @param string|null       $format
     * @param DateTimeZone|null $tz
     * @param class-string<T>   $class
     *
     * @return T
     *
     * @return DateTimeInterface|null
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     * @throws DateTimeFormatException
     */
    public function decodeDateTimeIfPresent(string $format = null, DateTimeZone $tz = null, $class = DateTime::class): ?DateTimeInterface
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeDateTime($format, $tz, $class);
        }
    }

    /**
     * Decode object.
     *
     * @param class-string<T>|null $class Object class.
     * @param T|null $object Decode into the given object.
     * @param bool $strict Check if the value in the container is an object
     *
     * @return ($class === null ? object : T)
     *
     * @template T
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     * @throws CodableException
     */
    public function decodeObject(?string $class = null, ?object $object = null, bool $strict = false)
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        }

        if ($strict && !is_object($this->getValue())) {
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), $class);
        }

        $decorator = $class !== null ? $this->getContext()->getDecorator($class) : null;

        if ($decorator instanceof DecodableDecorator) {
            return $decorator->decode($class, $this, $object);
        } elseif ($decorator !== null && is_a($decorator, StaticDecodableDecorator::class, true)) {
            return $decorator::decode($class, $this, $object);
        } elseif (is_callable($decorator)) {
            return call_user_func($decorator, $this, $object);
        } elseif (is_a($class, Decodable::class, true)) {
            return $class::decode($this, $object);
        } elseif (!is_object($this->getValue())) {
            // we do this check as one of the last things because certain classes like DateTime
            // are encoded to string literals
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), $class);
        } elseif ($object === null) {
            return $this->getValue();
        } else {
            foreach (get_object_vars($this->getValue()) as $k => $v) {
                $object->$k = $v;
            }

            return $object;
        }
    }

    /**
     * Decode object if present
     *
     * @param class-string<T>|null $class  Target class.
     * @param T|null               $object Decode into the given object (unless the object is not present).
     *
     * @return ($class === null ? object : T)|null
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     *
     * @template T
     */
    public function decodeObjectIfPresent(?string $class = null, ?object $object = null)
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeObject($class, $object);
        }
    }

    /**
     * Decode array.
     *
     * @template T
     *
     * @param callable|string|class-string<T>|null $iteratorOrElementType
     *
     * @return ($iteratorOrElementType is class-string<T> ? array<T> : array)
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeArray($iteratorOrElementType = null): array
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } elseif (!is_array($this->getValue()) && !is_object($this->getValue())) {
            throw new ValueTypeMismatchException($this->getPath(), gettype($this->getValue()), 'array');
        } else {
            $items = $this->getValue();
            if (is_object($items)) {
                $items = get_object_vars($items);
            }

            $iterator = $iteratorOrElementType;
            if (!is_callable($iterator)) {
                $elementType = $iteratorOrElementType;
                $iterator = fn ($c) => $c->decode($elementType);
            }

            return array_combine(
                array_keys($items),
                array_map(
                    fn ($k, $v) => $iterator(new self($v, $this->getContext(), $this, $k)),
                    array_keys($items),
                    array_values($items)
                )
            );
        }
    }

    /**
     * Decode array if present.
     *
     * @param callable|string|class-string<T>|null $iteratorOrElementType
     *
     * @return ($iteratorOrElementType is class-string<T> ? ?array<T> : ?array)
     *
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     *
     * @template T
     */
    public function decodeArrayIfPresent($iteratorOrElementType = null): ?array
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeArray($iteratorOrElementType);
        }
    }

    /**
     * Checks if the given key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function contains(string $key): bool
    {
        if (is_object($this->_value)) {
            return property_exists($this->_value, $key);
        } elseif (is_array($this->_value)) {
            return array_key_exists($key, $this->_value);
        } else {
            return false;
        }
    }

    /**
     * Returns the nested container for the given key.
     *
     * @param string $key
     *
     * @return DecodingContainer
     */
    public function nestedContainer(string $key): DecodingContainer
    {
        if (is_object($this->_value)) {
            $nestedValue = $this->_value->$key ?? null;
        } elseif (is_array($this->_value)) {
            $nestedValue = $this->_value[$key] ?? null;
        } else {
            $nestedValue = null;
        }

        return new DecodingContainer($nestedValue, $this->getContext()->createChildContext(), $this, $key);
    }

    /**
     * Get nested container for the given key.
     *
     * @param string $key
     *
     * @return DecodingContainer
     */
    public function get(string $key): DecodingContainer
    {
        return $this->nestedContainer($key);
    }

    /**
     * Check if the key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->contains($key);
    }

    /**
     * @deprecated Use {@see self::get()} instead to avoid the use of magic properties
     *
     * Get nested container for the given key.
     *
     * @param string $key
     *
     * @return DecodingContainer
     */
    public function __get(string $key): DecodingContainer
    {
        return $this->nestedContainer($key);
    }
}

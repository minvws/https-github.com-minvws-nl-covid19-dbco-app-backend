<?php
namespace DBCO\Shared\Application\Codable;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * Decoding container.
 *
 * @package DBCO\Shared\Application\Codable
 */
class DecodingContainer
{
    // NOTE: class members have a _ prefix to prevent clashes with
    //       the magic getter.

    /**
     * @var Context
     */
    private Context $_context;

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
     * @param Context                $context
     * @param DecodingContainer|null $parent
     * @param string|int|null        $key
     */
    public function __construct($value, Context $context, ?DecodingContainer $parent = null, $key = null)
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
     * Context.
     *
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->_context;
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
     * Decode string.
     *
     * @return string
     *
     * @throws ValueNotFoundException
     * @throws TypeMismatchException
     */
    public function decodeString(): string
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } else if (!is_string($this->getValue())) {
            throw new TypeMismatchException($this->getPath(), gettype($this->getValue()), 'string');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode string if present.
     *
     * @return string|null
     *
     * @throws TypeMismatchException
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
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeInt(): int
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } else if (!is_int($this->getValue())) {
            throw new TypeMismatchException($this->getPath(), gettype($this->getValue()), 'int');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode int if present.
     *
     * @return int|null
     *
     * @throws TypeMismatchException
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
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeFloat(): float
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } else if (!is_float($this->getValue())) {
            throw new TypeMismatchException($this->getPath(), gettype($this->getValue()), 'float');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode float if present.
     *
     * @return float|null
     *
     * @throws TypeMismatchException
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
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeBool(): bool
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } else if (!is_bool($this->getValue())) {
            throw new TypeMismatchException($this->getPath(), gettype($this->getValue()), 'bool');
        } else {
            return $this->getValue();
        }
    }

    /**
     * Decode bool if present.
     *
     * @return bool|null
     *
     * @throws TypeMismatchException
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
     * @param string|null       $format
     * @param DateTimeZone|null $tz
     *
     * @return DateTimeInterface
     *
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     * @throws DateTimeFormatException
     */
    public function decodeDateTime(string $format = null, DateTimeZone $tz = null): DateTimeInterface
    {
        $string = $this->decodeStringIfPresent();

        if ($format === null) {
            try {
                $dateTime = new DateTimeImmutable($string, $tz);
            } catch (Exception $e) {
                throw new DateTimeFormatException($this->getPath(), '<any>');
            }
        } else {
            $dateTime = DateTimeImmutable::createFromFormat($format, $string, $tz);
            if (!$dateTime) {
                throw new DateTimeFormatException($this->getPath(), $format);
            }
        }

        return $dateTime;
    }

    /**
     * Decode date time using the given format if present.
     *
     * @param string|null       $format
     * @param DateTimeZone|null $tz
     *
     * @return DateTimeInterface|null
     *
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     * @throws DateTimeFormatException
     */
    public function decodeDateTimeIfPresent(string $format = null, DateTimeZone $tz = null): ?DateTimeInterface
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeDateTime();
        }
    }

    /**
     * Decode object.
     *
     * @template T
     *
     * @param string $class
     *
     * @return T
     *
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeObject(string $class): object
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } else if (!is_object($this->getValue())) {
            throw new TypeMismatchException($this->getPath(), gettype($this->getValue()), 'object');
        }

        $decorator = $this->getContext()->getDecorator($class);
        if ($decorator !== null) {
            return $decorator::decode($class, $this);
        } else {
            return $class::decode($this);
        }
    }

    /**
     * Decode object if present
     *
     * @template T
     *
     * @param string $class
     *
     * @return T|null
     *
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeObjectIfPresent(string $class): ?object
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeObject($class);
        }
    }

    /**
     * Decode array.
     *
     * @template T
     *
     * @param callable $iterator
     *
     * @return array<T>
     *
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeArray(callable $iterator): array
    {
        if ($this->getValue() === null) {
            throw new ValueNotFoundException($this->getPath());
        } else if (!is_array($this->getValue())) {
            throw new TypeMismatchException($this->getPath(), gettype($this->getValue()), 'array');
        } else {
            return array_map(
                fn ($k, $v) => $iterator(new self($v, $this->getContext(), $this, $k)),
                array_keys($this->getValue()),
                $this->getValue()
            );
        }
    }

    /**
     * Decode array if present.
     *
     * @template T
     *
     * @param callable $iterator
     *
     * @return array<T>|null
     *
     * @throws TypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeArrayIfPresent(callable $iterator): ?array
    {
        if ($this->getValue() === null) {
            return null;
        } else {
            return $this->decodeArray($iterator);
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
        return is_object($this->getValue()) && property_exists($this->getValue(), $key);
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
        return new DecodingContainer($this->getValue()->$key ?? null, new Context($this->getContext()), $this, $key);
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
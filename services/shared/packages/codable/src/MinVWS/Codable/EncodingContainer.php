<?php

namespace MinVWS\Codable;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use stdClass;
use Traversable;

/**
 * Encoding container.
 *
 * @package MinVWS\Codable
 */
class EncodingContainer
{
    // NOTE: class members have a _ prefix to prevent clashes with
    //       the magic getter.

    /**
     * @var EncodingContext
     */
    private EncodingContext $_context;

    /**
     * @var mixed
     */
    private $_value;

    /**
     * @var EncodingContainer|null
     */
    private ?EncodingContainer $_parent;

    /**
     * @var string|int|null
     */
    private $_key;

    /**
     * Constructor.
     *
     * @param mixed                  $value
     * @param EncodingContext        $context
     * @param EncodingContainer|null $parent
     * @param string|int|null        $key
     */
    public function __construct(&$value, EncodingContext $context, ?EncodingContainer $parent = null, $key = null)
    {
        $this->_value = &$value;
        $this->_context = $context;
        $this->_parent = $parent;
        $this->_key = $key;
    }

    /**
     * Context.
     *
     * @return EncodingContext
     */
    public function getContext(): EncodingContext
    {
        return $this->_context;
    }

    public function getRoot(): self
    {
        if ($this->getParent() !== null) {
            return $this->getParent()->getRoot();
        }

        return $this;
    }

    /**
     * Returns the parent container.
     */
    public function getParent(): ?self
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
     * Encode value.
     *
     * @param mixed|null $value
     *
     * @throws ValueTypeMismatchException
     */
    public function encode($value): void
    {
        if (is_null($value)) {
            $this->encodeNull();
        } elseif (is_string($value)) {
            $this->encodeString($value);
        } elseif (is_int($value)) {
            $this->encodeInt($value);
        } elseif (is_bool($value)) {
            $this->encodeBool($value);
        } elseif (is_float($value)) {
            $this->encodeFloat($value);
        } elseif (is_object($value)) {
            $this->encodeObject($value);
        } elseif (is_array($value)) {
            $this->encodeArray($value);
        } else {
            // can only happen for resources
            throw new ValueTypeMismatchException($this->getPath(), gettype($value), '<non-resource>');
        }
    }

    /**
     * Encode null.
     */
    public function encodeNull(): void
    {
        $this->_value = null;
    }

    /**
     * Encode string.
     *
     * @param string|null $value
     */
    public function encodeString(?string $value): void
    {
        $this->_value = $value;
    }

    /**
     * Encode integer.
     *
     * @param int|null $value
     */
    public function encodeInt(?int $value): void
    {
        $this->_value = $value;
    }

    /**
     * Encode float.
     *
     * @param float|null $value
     */
    public function encodeFloat(?float $value): void
    {
        $this->_value = $value;
    }

    /**
     * Encode bool.
     *
     * @param bool|null $value
     */
    public function encodeBool(?bool $value): void
    {
        $this->_value = $value;
    }

    /**
     * Encode date time using the given format.
     *
     * @param DateTimeInterface|null $value
     * @param string|null            $format
     * @param DateTimeZone|null      $tz
     *
     * @throws DateTimeFormatException
     */
    public function encodeDateTime(?DateTimeInterface $value, string $format = null, DateTimeZone $tz = null): void
    {
        if (is_null($value)) {
            $this->_value = $value;
            return;
        }

        $format = $format ?: $this->getContext()->getDateTimeFormat();
        $tz = $tz ?: $this->getContext()->getDateTimeZone();
        try {
            if (version_compare(phpversion(), '8.0.0', '<')) {
                $dateTime = new DateTimeImmutable('@' . $value->getTimestamp(), $tz);
                // add support for 'p' timezone specifier on PHP < 8.0.0
                $format = str_replace('p', 'P', $format);
                $string = str_replace('+00:00', 'Z', $dateTime->format($format));
            } else {
                $dateTime = DateTimeImmutable::createFromInterface($value);
                $dateTime->setTimezone($tz);
                $string = $dateTime->format($format);
            }

            $this->encodeString($string);
        } catch (Exception $e) {
            throw new DateTimeFormatException($this->getPath(), $format);
        }
    }

    /**
     * Encode object.
     *
     * @param object|null $value
     */
    public function encodeObject(?object $value): void
    {
        if (is_null($value)) {
            $this->_value = $value;
            return;
        }

        $decorator = $this->getContext()->getDecorator(get_class($value));
        if ($decorator instanceof EncodableDecorator) {
            $decorator->encode($value, $this);
        } elseif ($decorator !== null && is_a($decorator, StaticEncodableDecorator::class, true)) {
            $decorator::encode($value, $this);
        } elseif (is_callable($decorator)) {
            call_user_func($decorator, $value, $this);
        } elseif ($value instanceof Encodable) {
            $value->encode($this);
        } else {
            // no encoder available for object class, try to use the JSON encoder
            $this->_value = json_decode(json_encode($value), $this->getContext()->useAssociativeArraysForObjects());
        }
    }

    /**
     * Encode array/traversable.
     *
     * @param array|Traversable|null $value
     */
    public function encodeArray($value, $iterator = null): void
    {
        if (is_null($value)) {
            $this->_value = null;
            return;
        }

        if (!is_callable($iterator)) {
            $iterator = fn ($c, $v) => $c->encode($v);
        }

        $arr = [];
        foreach ($value as $k => $v) {
            $c = new self($arr[$k], $this->getContext(), $this, $k);
            $iterator($c, $v);
        }

        $this->_value = $arr;
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
     * @return EncodingContainer
     */
    public function nestedContainer(string $key): EncodingContainer
    {
        if ($this->getContext()->useAssociativeArraysForObjects()) {
            if (!is_array($this->_value)) {
                $this->_value = [];
            }

            $nestedValue = &$this->_value[$key];
        } else {
            if (!is_object($this->_value)) {
                $this->_value = new stdClass();
            }

            $nestedValue = &$this->_value->$key;
        }

        return new self($nestedValue, $this->getContext()->createChildContext(), $this, $key);
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
     * @return EncodingContainer
     */
    public function __get(string $key): EncodingContainer
    {
        return $this->nestedContainer($key);
    }

    /**
     * Encodes the given property with the given value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws ValueTypeMismatchException
     */
    public function __set(string $key, $value)
    {
        $this->nestedContainer($key)->encode($value);
    }

    /**
     * Unsets the given property.
     *
     * @param string $key
     */
    public function __unset(string $key)
    {
        if (is_object($this->_value)) {
            unset($this->_value->$key);
        } elseif (is_array($this->_value)) {
            unset($this->_value[$key]);
        }
    }
}

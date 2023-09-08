<?php

declare(strict_types=1);

namespace App\Schema;

use App\Schema\Traits\NewInstanceWithVersion;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContainer;
use stdClass;

use function assert;
use function get_object_vars;
use function is_numeric;
use function is_string;
use function method_exists;
use function ucfirst;

/**
 * Base class for object dynamically implementing a versioned schema.
 *
 * @implements SchemaObjectFactory<static>
 */
class Entity implements EntityObject, SchemaObjectFactory, Encodable, JsonSerializable, ArrayAccess, Arrayable
{
    use NewInstanceWithVersion;

    private SchemaVersion $schemaVersion;

    private stdClass $data;

    private ?OwnerProxy $ownerProxy = null;

    private bool $isDirty = false;

    public function __construct(SchemaVersion $schemaVersion)
    {
        $this->schemaVersion = $schemaVersion;

        $this->data = new stdClass();
        foreach ($schemaVersion->getFields() as $field) {
            $this->data->{$field->getName()} = null;
        }
    }

    /**
     * Returns the internal data object.
     *
     * Use very carefully!
     */
    public function getData(): stdClass
    {
        return $this->data;
    }

    /**
     * Create a new instance for the given schema version.
     *
     * Should not be used directly, use the SchemaVersion::newInstance() method instead!
     *
     * @param SchemaVersion<static> $schemaVersion
     *
     * @return static
     */
    final public static function newUninitializedInstanceWithSchemaVersion(SchemaVersion $schemaVersion): SchemaObject
    {
        return new static($schemaVersion);
    }

    final public function getSchemaVersion(): SchemaVersion
    {
        return $this->schemaVersion;
    }

    /**
     * Returns the owner instance for this entity.
     */
    final public function getOwner(): ?SchemaObject
    {
        return $this->getOwnerProxy()->getOwner();
    }

    /**
     * Returns the owner proxy for this entity.
     *
     * An owner can be any schema object instance of which this instance is a direct child. A child might not be
     * attached yet to its owner in which case the proxy stores pending data updates and tries to return owner values
     * based on the pending updates it has stored. Once the child is attached to the owner any pending update is
     * immediately applied and get/set calls will be forwarded directly to the owner instance.
     */
    final protected function getOwnerProxy(): OwnerProxy
    {
        if ($this->ownerProxy === null) {
            // lazily instantiate the owner proxy
            $this->ownerProxy = new OwnerProxy();
        }

        return $this->ownerProxy;
    }

    /**
     * Is owner instance attached?
     */
    final protected function isOwnerAttached(): bool
    {
        return $this->getOwnerProxy()->isOwnerAttached();
    }

    /**
     * Attach owner instance.
     *
     * This can be any schema object instance of which this instance is a direct child.
     */
    final public function attachOwner(SchemaObject $owner): void
    {
        if ($this->getOwnerProxy()->isOwnerAttached() && $this->getOwnerProxy()->getOwner() === $owner) {
            return; // already attached
        }

        $this->getOwnerProxy()->attachOwner($owner);
        $this->onAttachOwner($owner);
    }

    /**
     * Invoked when the owner instance is attached.
     */
    protected function onAttachOwner(SchemaObject $owner): void
    {
    }

    /**
     * Detach owner instance.
     */
    final protected function detachOwner(): void
    {
        if ($this->getOwnerProxy()->getOwner() !== null) {
            $this->onDetachOwner($this->getOwnerProxy()->getOwner());
            $this->getOwnerProxy()->detachOwner();
        }
    }

    /**
     * Invoked when the owner instance is detached.
     */
    protected function onDetachOwner(SchemaObject $owner): void
    {
    }

    /**
     * Is this entity dirty?
     */
    public function isDirty(): bool
    {
        if ($this->isDirty) {
            return true;
        }

        foreach (get_object_vars($this->data) as $value) {
            if ($value instanceof self && $value->isDirty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset dirty.
     */
    public function resetDirty(): void
    {
        $this->isDirty = false;

        foreach (get_object_vars($this->data) as $value) {
            if ($value instanceof self) {
                $value->resetDirty();
            }
        }
    }

    /**
     * Encode instance.
     *
     * @throws CodableException
     */
    public function encode(EncodingContainer $container): void
    {
        $this->getSchemaVersion()->encode($container, $this);
    }

    /**
     * Returns the value for the named field.
     *
     * @param string $name Field name.
     * @param bool $throwException If the field doesn't exist an exception will be thrown.
     */
    protected function getRawFieldValue(string $name, bool $throwException = true): mixed
    {
        if ($name === $this->getSchemaVersion()->getSchema()->getOwnerFieldName()) {
            return $this->getOwnerProxy();
        }

        $field = $this->getSchemaVersion()->getField($name);

        if ($field === null && $throwException) {
            throw new InvalidArgumentException('Invalid field: ' . $name);
        }

        if ($field === null) {
            return null;
        }

        if ($field->isProxyForOwnerField()) {
            return $this->getOwnerProxy()->{$field->getProxyForOwnerField()};
        }

        return $this->data->$name ?? null;
    }

    /**
     * Returns the value for the named field.
     *
     * If a method `get<Name>FieldValue` exists, this method will be called with a getter callback.
     * If no such method exists, `getRawFieldValue` will be called.
     *
     * @param string $name Field name.
     * @param bool $throwException If no getter method exists and the field doesn't exist an exception will be thrown.
     */
    protected function getFieldValue(string $name, bool $throwException = true): mixed
    {
        $method = 'get' . ucfirst($name) . 'FieldValue';
        if (method_exists($this, $method)) {
            return $this->$method(fn () => $this->getRawFieldValue($name, $throwException));
        }

        return $this->getRawFieldValue($name, $throwException);
    }

    /**
     * Sets the value for the named field.
     *
     * @param string $name Field name.
     * @param mixed $value Value.
     * @param bool $throwException If the field doesn't exist an exception will be thrown.
     */
    protected function setRawFieldValue(string $name, mixed $value, bool $throwException = true): void
    {
        $field = $this->getSchemaVersion()->getField($name);

        if ($field === null && $throwException) {
            throw new InvalidArgumentException('Invalid field: ' . $name);
        }

        if ($field === null) {
            return;
        }

        if ($field->isProxyForOwnerField()) {
            $this->getOwnerProxy()->{$field->getProxyForOwnerField()} = $value;
            return;
        }

        if ($value instanceof self) {
            $oldValue = $this->data->$name ?? null;
            if ($oldValue !== $value && $oldValue instanceof self) {
                $oldValue->detachOwner();
            }

            $value->attachOwner($this);
        }

        $field->assign($this->data, $value);
        $this->isDirty = true;
    }

    /**
     * Sets the value for the named field.
     *
     * If a method `set<Name>FieldValue` exists, this method will be called with the given value and a
     * setter callback. If no such method exists, `setRawFieldValue` will be called.
     *
     * @param string $name Field name.
     * @param mixed $value Value.
     * @param bool $throwException If no setter method exists and the field doesn't exist an exception will be thrown.
     */
    protected function setFieldValue(string $name, mixed $value, bool $throwException = true): void
    {
        $method = 'set' . ucfirst($name) . 'FieldValue';
        if (method_exists($this, $method)) {
            $this->$method($value, fn ($value) => $this->setRawFieldValue($name, $value, $throwException));
        } else {
            $this->setRawFieldValue($name, $value, $throwException);
        }
    }

    /**
     * Is field set?
     */
    public function __isset(string $name): bool
    {
        return $this->getFieldValue($name, false) !== null;
    }

    /**
     * Returns the current value for the given field.
     */
    public function __get(string $name): mixed
    {
        return $this->getFieldValue($name);
    }

    /**
     * Sets the value for the given field.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setFieldValue($name, $value);
    }

    /**
     * Unsets the given field.
     */
    public function __unset(string $name): void
    {
        $this->setFieldValue($name, null, false);
    }

    /**
     * Serialize to JSON.
     */
    public function jsonSerialize(): object
    {
        $encoder = new Encoder();
        return (object) $encoder->encode($this);
    }

    // @codeCoverageIgnoreStart
    // Code coverage ignore was added because it's kind of hard to currently (Unit!)test Entity. It's also currently
    // not fully tested anyways. Created new issue for this @ https://egeniq.atlassian.net/browse/DBCO-5167
    public function offsetExists(mixed $offset): bool
    {
        if (!(is_string($offset) || is_numeric($offset))) {
            return false;
        }

        return $this->__isset((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        assert(is_string($offset) || is_numeric($offset));

        return $this->__get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        assert(is_string($offset) || is_numeric($offset));

        $this->__set((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        assert(is_string($offset) || is_numeric($offset));

        $this->__unset((string) $offset);
    }

    public function toArray(): array
    {
        return (array) $this->getData();
    }
    // @codeCoverageIgnore
}

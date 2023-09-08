<?php

declare(strict_types=1);

namespace App\Schema;

class OwnerProxy
{
    private ?SchemaObject $owner = null;

    /** @var array */
    private array $pendingOwnerData = [];

    /**
     * Returns the owner object for this entity.
     *
     * This can be any schema object instance of which the entity instance is a direct child.
     */
    public function getOwner(): ?SchemaObject
    {
        return $this->owner;
    }

    /**
     * Is owner object attached?
     */
    final public function isOwnerAttached(): bool
    {
        return isset($this->owner);
    }

    /**
     * Attach owner object.
     *
     * This can be any schema object instance of which this instance is a direct child.
     */
    public function attachOwner(SchemaObject $owner): void
    {
        $this->owner = $owner;

        foreach ($this->pendingOwnerData as $name => $value) {
            $owner->$name = $value;
        }

        $this->pendingOwnerData = [];
    }

    /**
     * Detach owner instance.
     */
    public function detachOwner(): void
    {
        $this->owner = null;
    }

    /**
     * Is field set?
     */
    public function __isset(string $name): bool
    {
        if ($this->isOwnerAttached()) {
            return isset($this->owner->$name);
        }

        return isset($this->pendingOwnerData[$name]);
    }

    /**
     * Returns the current value for the given field.
     */
    public function __get(string $name): mixed
    {
        if ($this->isOwnerAttached()) {
            return $this->owner->$name ?? null;
        }

        return $this->pendingOwnerData[$name] ?? null;
    }

    /**
     * Sets the value for the given field.
     */
    public function __set(string $name, mixed $value): void
    {
        if ($this->isOwnerAttached()) {
            $this->owner->$name = $value;
        } else {
            $this->pendingOwnerData[$name] = $value;
        }
    }

    /**
     * Unsets the given field.
     */
    public function __unset(string $name): void
    {
        if ($this->isOwnerAttached()) {
            unset($this->owner->$name);
        } else {
            unset($this->pendingOwnerData[$name]);
        }
    }
}

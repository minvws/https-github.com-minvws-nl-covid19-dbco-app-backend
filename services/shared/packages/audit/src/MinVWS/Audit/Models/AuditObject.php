<?php

namespace MinVWS\Audit\Models;

use Closure;

/**
 * Represents an object in an audit event.
 *
 * @package MinVWS\Audit\Models
 */
class AuditObject
{
    private string $type;
    private string $identifier;
    private ?array $details = null;

    private function __construct(string $type, string $identifier)
    {
        $this->type = $type;
        $this->identifier = $identifier;
    }

    /**
     * Creates a new audit object.
     */
    public static function create(string $type, string $identifier = ''): self
    {
        return new self($type, $identifier);
    }

    /**
     * Create audit objects array using the given mapper.
     *
     * @return self[]
     */
    public static function createArray(array $objects, Closure $mapper): array
    {
        return array_map($mapper, $objects);
    }

    /**
     * Modify type.
     */
    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Modify identifier.
     */
    public function identifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Sets custom data as key/value pair
     */
    public function detail(string $key, $details): self
    {
        $this->details[$key] = $details;
        return $this;
    }

    /**
     * Returns the object type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the object identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Returns all custom data for this object
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }
}

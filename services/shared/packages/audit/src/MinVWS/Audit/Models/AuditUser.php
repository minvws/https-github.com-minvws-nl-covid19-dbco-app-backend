<?php

namespace MinVWS\Audit\Models;

/**
 * Represents an user in an audit event.
 *
 * @package MinVWS\Audit\Models
 */
class AuditUser
{
    private string $type;
    private string $identifier;
    private ?string $name = null;
    private ?array $roles = null;
    private ?array $purposes = null;
    private ?array $details = null;
    private ?string $ip = null;

    private function __construct(string $type, string $identifier)
    {
        $this->type = $type;
        $this->identifier = $identifier;
    }

    /**
     * Creates a new audit object.
     */
    public static function create(string $type, string $identifier): self
    {
        return new self($type, $identifier);
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
     * Sets the name for the user.
     */
    public function name(?string $name): self
    {
        $this->name = $name;
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
     * Sets the ip for the user.
     */
    public function ip(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @param array<string>|null $roles
     */
    public function roles(?array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function purposes(?array $purposes): self
    {
        $this->purposes = $purposes;
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
     * Returns the name for this user.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns the roles for this user
     * @return array<string>|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getPurposes(): ?array
    {
        return $this->purposes;
    }

    /**
     * Returns all custom data for this user
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * Returns the ip for this user.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }
}

<?php

declare(strict_types=1);

namespace App\Schema\Validation;

final class ValidationContext
{
    public const FATAL = ValidationRules::FATAL;
    public const WARNING = ValidationRules::WARNING;
    public const NOTICE = ValidationRules::NOTICE;
    public const OSIRIS_APPROVED = 'osiris_approved';
    public const OSIRIS_FINISHED = 'osiris_finished';

    private ?ValidationContext $parent;

    private ?string $key;

    private ?string $level = null;

    /** @var array */
    private array $data = [];

    private ?object $target = null;

    public function __construct(?ValidationContext $parent = null, ?string $key = null)
    {
        $this->parent = $parent;
        $this->key = $key;

        if ($parent !== null) {
            $this->setTarget($parent->getTarget()->$key ?? null);
        }
    }

    /**
     * Path for the validation rules.
     */
    public function getPath(): string
    {
        return $this->getPrefixPath() . ($this->key ?? '');
    }

    /**
     * Prefix path for the validation rule.
     */
    public function getPrefixPath(): string
    {
        if ($this->getParent() === null) {
            return '';
        }

        $path = $this->getParent()->getPath();
        return empty($path) ? '' : $path . '.';
    }

    /**
     * Parent context.
     */
    public function getParent(): ?ValidationContext
    {
        return $this->parent;
    }

    /**
     * Root context?
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * Returns the root context.
     */
    public function getRoot(): ValidationContext
    {
        return $this->getParent() === null ? $this : $this->getParent()->getRoot();
    }

    /**
     * Sets the current validation level.
     *
     * @return $this
     */
    public function setLevel(string $level): self
    {
        $this->getRoot()->level = $level;
        return $this;
    }

    /**
     * Current validation level.
     */
    public function getLevel(): string
    {
        return $this->getRoot()->level ?? self::FATAL;
    }

    /**
     * Returns custom provided data available in the context.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->getRoot()->data;
    }

    /**
     * Sets the custom data for the context.
     *
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->getRoot()->data = $data;
    }

    /**
     * Returns the value for a given key in the custom provided data available in the context.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        return $this->getData()[$key] ?? $default;
    }

    /**
     * Returns the target object to which the data will be decoded.
     */
    public function getTarget(): ?object
    {
        return $this->target;
    }

    /**
     * Sets the target object to which the data will be decoded.
     */
    public function setTarget(?object $target): void
    {
        $this->target = $target;
    }

    /**
     * Create a nested context instance.
     */
    public function nestedContext(string $key): ValidationContext
    {
        return new self($this, $key);
    }
}

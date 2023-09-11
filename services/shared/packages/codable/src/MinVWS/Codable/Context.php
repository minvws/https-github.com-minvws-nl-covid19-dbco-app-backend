<?php

namespace MinVWS\Codable;

/**
 * Context base.
 *
 * Can be used to register decorators and
 * contextual values for encoding/decoding.
 *
 * @package MinVWS\Codable
 */
abstract class Context
{
    /**
     * @var self|null
     */
    private ?self $parent;

    /**
     * @var string|null
     */
    private ?string $mode = null;

    private ?string $view = null;

    /**
     * @var array
     */
    private array $values = [];

    /**
     * @var array
     */
    private array $decorators = [];

    /**
     * Constructor.
     *
     * @param static|null $parent
     */
    public function __construct(?self $parent = null)
    {
        $this->parent = $parent;
        $this->init();
    }

    protected function init(): void
    {
    }

    /**
     * Optional mode.
     *
     * @param string|null $mode
     */
    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * Returns the optional mode.
     *
     * @return string|null
     */
    public function getMode(): ?string
    {
        if (isset($this->mode)) {
            return $this->mode;
        } elseif ($this->parent !== null) {
            return $this->parent->getMode();
        } else {
            return null;
        }
    }

    /**
     * Optional view.
     *
     * @param string|null $view
     */
    public function setView(?string $view): void
    {
        $this->view = $view;
    }

    /**
     * Returns the optional view.
     *
     * @return string|null
     */
    public function getView(): ?string
    {
        if (isset($this->view)) {
            return $this->view;
        } elseif ($this->parent !== null) {
            return $this->parent->getView();
        } else {
            return null;
        }
    }

    /**
     * Returns the root context.
     *
     * @return static|null
     */
    public function getRoot(): ?self
    {
        if ($this->getParent() !== null) {
            return $this->getParent()->getRoot();
        }

        return $this;
    }

    /**
     * Returns the parent context.
     *
     * @return static|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Returns the context value for the given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue(string $key)
    {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        } elseif ($this->parent !== null) {
            return $this->parent->getValue($key);
        } else {
            return null;
        }
    }

    /**
     * Sets the context value for the given key.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setValue(string $key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Unset value.
     *
     * This is different from setting the value to null as that would prevent the context asking
     * its parent for a value. By unsetting the value the context will try if its parent context
     * has a value if requested by getValue.
     *
     * @param string $key
     */
    public function unsetValue(string $key)
    {
        unset($this->values[$key]);
    }

    /**
     * Returns the decorator for the given class (if registered).
     *
     * @param string $class
     *
     * @return class-string|object|callable|null
     */
    public function getDecorator(string $class)
    {
        $keys = $this->getDecoratorKeysForClass($class);
        $decorators = $this->getDecorators();

        foreach ($keys as $key) {
            if (isset($decorators[$key])) {
                return $decorators[$key];
            }
        }

        return null;
    }

    /**
     * Returns all the possible decorator keys for the given class ordered by importance
     * (e.g. the class itself, but also parent classes and interfaces it implements).
     *
     * @param string $class
     *
     * @return string[]
     */
    private function getDecoratorKeysForClass(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $keys[] = $class;
        for ($c = $class; $c !== false; $c = get_parent_class($c)) {
            $keys[] = $c;
        }

        return array_merge($keys, class_implements($class));
    }

    /**
     * Merge decorators.
     *
     * @return array
     */
    private function getDecorators(): array
    {
        if ($this->getParent() === null) {
            return $this->decorators;
        } else {
            return array_merge($this->getParent()->getDecorators(), $this->decorators);
        }
    }

    /**
     * Register external decorator for the given class.
     *
     * @param class-string                 $class     Class name of the class for which we need a decorator.
     * @param class-string|object|callable $decorator Class name of the decorator class, instance or callable.
     */
    public function registerDecorator(string $class, $decorator): void
    {
        $this->decorators[$class] = $decorator;
    }

    /**
     * Unregister decorator for the given class.
     *
     * @param string $class
     */
    public function unregisterDecorator(string $class)
    {
        unset($this->decorators[$class]);
    }

    /**
     * Create child context.
     *
     * @return static
     */
    abstract public function createChildContext(): self;
}

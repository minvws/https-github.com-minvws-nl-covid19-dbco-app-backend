<?php
namespace DBCO\Shared\Application\Codable;

/**
 * Decoder context.
 *
 * Can be used to register decodable decorators and
 * contextual values for decoding.
 *
 * @package DBCO\Shared\Application\Codable
 */
class Context
{
    /**
     * @var Context|null
     */
    private ?Context $parent;

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
     * @param Context|null $parent
     */
    public function __construct(?Context $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent context.
     *
     * @return Context|null
     */
    public function getParent(): ?Context
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
        } else if ($this->parent !== null) {
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
     * @return string|null
     */
    public function getDecorator(string $class): ?string
    {
        if (array_key_exists($class, $this->decorators)) {
            return $this->decorators[$class];
        } else if ($this->parent !== null) {
            return $this->parent->getDecorator($class);
        } else {
            return null;
        }
    }

    /**
     * Register external decodable decorator for the given class.
     *
     * @param class-string $class          Class name of the class that needs to be decoded.
     * @param class-string $decoratorClass Class name of the decodable decorator class.
     */
    public function registerDecorator(string $class, string $decoratorClass): void
    {
        $this->decorators[$class] = $decoratorClass;
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
}
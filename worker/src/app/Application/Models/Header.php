<?php
namespace DBCO\Worker\Application\Models;

/**
 * Represents an HTTP header.
 */
class Header
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string[]
     */
    public array $values;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $values
     */
    public function __construct(string $name, array $values)
    {
        $this->name = $name;
        $this->values = $values;
    }
}

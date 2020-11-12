<?php
namespace DBCO\Worker\Application\Models;

/**
 * Represents a pairing request case.
 */
class PairingRequestCase
{
    /**
     * @var string
     */
    public string $id;

    /**
     * Constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }
}

<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Client case.
 */
class ClientCase
{
    /**
     * @var string
     */
    public string $uuid;

    /**
     * Constructor.
     */
    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }
}

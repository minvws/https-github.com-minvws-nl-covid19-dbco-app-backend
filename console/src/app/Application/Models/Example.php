<?php
namespace App\Application\Models;

/**
 * Example model.
 */
class Example
{
    const STATUS_NEW      = 'new';
    const STATUS_PREPARED = 'prepared';
    const STATUS_EXPORTED = 'exported';

    /**
     * Identifier.
     *
     * @var string
     */
    public string $id;

    /**
     * Status.
     *
     * @var string
     */
    public string $status;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $status
     */
    public function __construct(string $id, string $status = self::STATUS_NEW)
    {
        $this->id = $id;
        $this->status = $status;
    }
}

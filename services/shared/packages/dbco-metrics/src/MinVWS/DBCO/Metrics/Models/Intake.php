<?php

namespace MinVWS\DBCO\Metrics\Models;

use DateTimeInterface;

/**
 * Metrics intake data.
 */
class Intake
{
    /**
     * Event identifier.
     *
     * @var string
     */
    public string $uuid;

    /**
     * Event type.
     *
     * @var string
     */
    public string $type;

    /**
     * Event data.
     *
     * @var array
     */
    public array $data;

    /**
     * @var DateTimeInterface
     */
    public DateTimeInterface $createdAt;

    public function __construct(string $uuid, string $type, array $data, DateTimeInterface $createdAt)
    {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->data = $data;
        $this->createdAt = $createdAt;
    }
}

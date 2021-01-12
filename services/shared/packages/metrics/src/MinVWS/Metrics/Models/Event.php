<?php
namespace MinVWS\Metrics\Models;

use DateTimeInterface;

/**
 * Metrics event.
 */
class Event
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
     * Export data.
     *
     * @var array
     */
    public array $exportData;

    /**
     * @var DateTimeInterface
     */
    public DateTimeInterface $createdAt;

    /**
     * Constructor.
     *
     * @param string            $uuid
     * @param string            $type
     * @param array             $data
     * @param array             $exportData
     * @param DateTimeInterface $createdAt
     */
    public function __construct(string $uuid, string $type, array $data, array $exportData, DateTimeInterface $createdAt)
    {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->data = $data;
        $this->exportData = $exportData;
        $this->createdAt = $createdAt;
    }
}
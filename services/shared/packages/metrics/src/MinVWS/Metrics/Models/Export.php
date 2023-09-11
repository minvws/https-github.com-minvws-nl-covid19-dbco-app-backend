<?php

namespace MinVWS\Metrics\Models;

use DateTimeInterface;

/**
 * Metrics export.
 */
class Export
{
    public const STATUS_INITIAL  = 'initial';
    public const STATUS_EXPORTED = 'exported';
    public const STATUS_UPLOADED = 'uploaded';

    /**
     * @var string
     */
    public string $uuid;

    /**
     * @var string
     */
    public string $status;

    /**
     * @var DateTimeInterface
     */
    public DateTimeInterface $createdAt;

    /**
     * @var string|null
     */
    public ?string $filename;

    /**
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $exportedAt;

    /**
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $uploadedAt;

    /**
     * @var int|null
     */
    public ?int $itemCount = null;


    public function __construct(string $uuid, string $status, DateTimeInterface $createdAt, ?string $filename = null, ?DateTimeInterface $exportedAt = null, ?DateTimeInterface $uploadedAt = null, ?int $itemCount = null)
    {
        $this->uuid = $uuid;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->filename = $filename;
        $this->exportedAt = $exportedAt;
        $this->uploadedAt = $uploadedAt;
        $this->itemCount = $itemCount;
    }
}

<?php
namespace  DBCO\HealthAuthorityAPI\Application\Models;

use DateTimeImmutable;

/**
 * Task.
 */
class Task
{
    /**
     * @var string
     */
    public string $uuid;

    /**
     * @var string
     */
    public string $taskType;

    /**
     * @var string|null
     */
    public ?string $source;

    /**
     * @var string|null
     */
    public ?string $label;

    /**
     * @var string|null
     */
    public ?string $taskContext;

    /**
     * @var string|null
     */
    public ?string $category;

    /**
     * @var string|null
     */
    public ?string $communication;

    /**
     * @var DateTimeImmutable|null
     */
    public ?DateTimeImmutable $dateOfLastExposure;
}

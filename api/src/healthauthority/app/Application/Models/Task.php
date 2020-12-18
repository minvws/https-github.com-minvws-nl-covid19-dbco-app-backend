<?php
namespace  DBCO\HealthAuthorityAPI\Application\Models;

use DateTimeInterface;

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
     * @var string
     */
    public string $source;

    /**
     * @var string|null
     */
    public ?string $label;

    /**
     * @var string|null
     */
    public ?string $derivedLabel = null;

    /**
     * @var string|null
     */
    public ?string $taskContext = null;

    /**
     * @var string
     */
    public string $category;

    /**
     * @var string
     */
    public string $communication;

    /**
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $dateOfLastExposure = null;

    /**
     * @var QuestionnaireResult|null
     */
    public ?QuestionnaireResult $questionnaireResult = null;
}

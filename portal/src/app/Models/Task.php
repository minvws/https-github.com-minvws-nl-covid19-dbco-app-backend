<?php

namespace App\Models;

use Jenssegers\Date\Date;

class Task
{
    public const TASK_DATA_INCOMPLETE = 'incomplete';
    public const TASK_DATA_CONTACTABLE = 'contactable';
    public const TASK_DATA_COMPLETE = 'complete';

    public string $uuid;

    public string $caseUuid;

    public string $taskType;

    public string $source;

    public ?string $label;

    public ?string $derivedLabel;

    public ?string $category;

    public ?string $taskContext;

    public ?string $nature;

    public ?Date $dateOfLastExposure;

    public string $communication;

    public bool $informedByIndex;

    public ?Date $createdAt = null;

    public ?Date $updatedAt = null;

    public ?array $answers;

    // Filled upon submit, indicates which questionnaire the user filled
    public ?string $questionnaireUuid;

    public string $progress = self::TASK_DATA_INCOMPLETE;

    public ?string $exportId = null;

    public ?Date $exportedAt = null;
    public ?Date $copiedAt = null;
}

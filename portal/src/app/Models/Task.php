<?php

namespace App\Models;

use Jenssegers\Date\Date;

class Task
{
    public const TASK_DATA_INCOMPLETE = 'incomplete';
    public const TASK_DATA_CONTACTABLE = 'contact';
    public const TASK_DATA_COMPLETE = 'compelte';

    public string $uuid;

    public string $caseUuid;

    public string $taskType;

    public string $source;

    public string $label;

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

    public int $progress = 0;

    public ?string $exportId = null;

    public ?Date $exportedAt = null;

    /**
     * @return bool true if the task has enough submitted answers to be contacted
     */
    public function hasContactData(): bool
    {
        // No questionnaire means no answer have been provided at all
        if ($this->questionnaireUuid === null) {
            return false;
        }
    }

    /**
     * @return bool true if the task has completed all answers
     */
    public function hasAllData(): bool
    {
        if (!$this->hasContactData()) {
            return false;
        }
    }
}

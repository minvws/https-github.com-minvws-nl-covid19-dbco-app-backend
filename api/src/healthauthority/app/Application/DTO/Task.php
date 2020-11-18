<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use  DBCO\HealthAuthorityAPI\Application\Models\Task as TaskModel;
use JsonSerializable;

/**
 * Task DTO.
 *
 * @package  DBCO\HealthAuthorityAPI\Application\Actions
 */
class Task implements JsonSerializable
{
    /**
     * @var TaskModel $task
     */
    private TaskModel $task;

    /**
     * Constructor.
     *
     * @param TaskModel $task
     */
    public function __construct(TaskModel $task)
    {
        $this->task = $task;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->task->uuid,
            'taskType' => $this->task->taskType,
            'source' => $this->task->source,
            'label' => $this->task->label,
            'taskContext' => $this->task->taskContext,
            'category' => $this->task->category,
            'communication' => $this->task->communication,
            'dateOfLastExposure' =>
                $this->task->dateOfLastExposure !== null ? $this->task->dateOfLastExposure->format('Y-m-d') : null
        ];
    }
}

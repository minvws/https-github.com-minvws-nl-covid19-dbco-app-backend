<?php
declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\Models\Task as TaskModel;
use JsonSerializable;

/**
 * Task DTO.
 *
 * @package App\Application\Actions
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
            'context' => $this->task->context,
            'category' => $this->task->category,
            'communication' => $this->task->communication
        ];
    }
}

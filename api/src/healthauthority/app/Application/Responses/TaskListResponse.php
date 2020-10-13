<?php
namespace App\Application\Responses;

use App\Application\DTO\Task;
use App\Application\Models\TaskList;
use DBCO\Application\Responses\Response;
use JsonSerializable;

/**
 * Task list response.
 */
class TaskListResponse extends Response implements JsonSerializable
{
    /**
     * @var TaskList
     */
    private TaskList $taskList;

    /**
     * Constructor.
     *
     * @param TaskList $taskList
     */
    public function __construct(TaskList $taskList)
    {
       $this->taskList = $taskList;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return ['tasks' => array_map(fn ($t) => new Task($t), $this->taskList->tasks)];
    }
}

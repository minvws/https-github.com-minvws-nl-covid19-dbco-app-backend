<?php
namespace DBCO\HealthAuthorityAPI\Application\Responses;

use DBCO\HealthAuthorityAPI\Application\DTO\Task;
use DBCO\HealthAuthorityAPI\Application\Models\TaskList;
use DBCO\Shared\Application\Responses\Response;
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

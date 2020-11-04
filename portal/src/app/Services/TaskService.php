<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Jenssegers\Date\Date;

class TaskService
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository) {
        $this->taskRepository = $taskRepository;
    }

    public function getTask(string $taskUuid) {
        return $this->taskRepository->getTask($taskUuid);
    }

    public function linkTaskToExport(Task $task, string $exportId): void
    {
        $task->exportId = $exportId;
        $task->exportedAt = Date::now();
        $this->taskRepository->updateTask($task);
    }
}

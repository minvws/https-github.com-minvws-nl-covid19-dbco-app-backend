<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;

class TaskService
{
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository) {
        $this->taskRepository = $taskRepository;
    }

    public function getTask(string $taskUuid) {
        return $this->taskRepository->getTask($taskUuid);
    }

    public function linkTaskToHpzone(Task $task, string $hpzoneId): void
    {
        $task->hpzoneId = $hpzoneId;
        $this->taskRepository->updateTask($task);
    }
}

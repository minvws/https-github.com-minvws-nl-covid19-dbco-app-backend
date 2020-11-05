<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Jenssegers\Date\Date;

class TaskService
{
    private TaskRepository $taskRepository;

    private CaseService $caseService;

    public function __construct(TaskRepository $taskRepository, CaseService $caseService)
    {
        $this->taskRepository = $taskRepository;
        $this->caseService = $caseService;
    }

    public function getTask(string $taskUuid)
    {
        return $this->taskRepository->getTask($taskUuid);
    }

    public function canAccess(Task $task)
    {
        $case = $this->caseService->getCase($task->caseUuid);
        return $this->caseService->canAccess($case);
    }

    public function linkTaskToExport(Task $task, string $exportId): void
    {
        $task->exportId = $exportId;
        $task->exportedAt = Date::now();
        $this->taskRepository->updateTask($task);
    }
}

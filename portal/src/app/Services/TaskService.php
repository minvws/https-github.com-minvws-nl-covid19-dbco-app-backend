<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\AnswerRepository;
use App\Repositories\TaskRepository;
use Illuminate\Support\Collection;
use Jenssegers\Date\Date;

class TaskService
{
    private TaskRepository $taskRepository;
    private AnswerRepository $answerRepository;
    private CaseService $caseService;

    public function __construct(TaskRepository $taskRepository,
                                AnswerRepository $answerRepository,
                                CaseService $caseService)
    {
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
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

    public function getAllAnswersByTask(string $taskUuid): Collection
    {
        return $this->answerRepository->getAllAnswersByTask($taskUuid);
    }
}

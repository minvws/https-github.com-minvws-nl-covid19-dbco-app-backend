<?php

namespace App\Services;

use App\Models\CovidCase;
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

    public function getTask(string $taskUuid): Task
    {
        return $this->taskRepository->getTask($taskUuid);
    }

    public function getTasks(string $caseUuid): array
    {
        return $this->taskRepository->getTasks($caseUuid)->all();
    }

    public function canAccess(Task $task): bool
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

    public function createTask(string $caseUuid, string $label, ?string $context, string $category, string $communication, ?Date $dateOfLastExposure): Task
    {
        return $this->taskRepository->createTask($caseUuid, $label, $context, $category, $communication, $dateOfLastExposure);
    }

    public function updateTask(Task $task): bool
    {
        return $this->taskRepository->updateTask($task);
    }

    public function deleteTask(Task $task): bool
    {
        return $this->taskRepository->deleteTask($task);
    }
}

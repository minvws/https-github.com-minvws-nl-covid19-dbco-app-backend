<?php

namespace App\Services;

use App\Repositories\CaseRepository;
use App\Repositories\TaskRepository;
use App\Models\CovidCase;
use Illuminate\Support\Collection;
use Jenssegers\Date\Date;


class CaseService
{
    private CaseRepository $caseRepository;
    private TaskRepository $taskRepository;
    private AuthenticationService $authService;

    public function __construct(CaseRepository $caseRepository, TaskRepository $taskRepository, AuthenticationService $authService)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->authService = $authService;

    }

    public function createDraftCase(): CovidCase
    {
        $owner = $this->authService->getAuthenticatedUser();
        return $this->caseRepository->createCase($owner, CovidCase::STATUS_DRAFT);
    }

    public function getCase($caseUuid): ?CovidCase
    {
        $case = $this->caseRepository->getCase($caseUuid);
        if ($case) {
            $case->tasks = $this->taskRepository->getTasks($caseUuid)->all();
        }
        return $case;
    }

    public function myCases(): Collection
    {
        return $this->caseRepository->getCasesByUser($this->authService->getAuthenticatedUser());
    }

    /**
     * Check if the current user has access to a case
     * @param CovidCase $case The case to check
     * @return bool True if access is ok
     */
    public function canAccess(CovidCase $case): bool
    {
        $user = $this->authService->getAuthenticatedUser();
        return $user->id == $case->owner;
    }

    public function updateCase(CovidCase $case)
    {
        $this->caseRepository->updateCase($case);
    }

    public function createOrUpdateTask($caseUuid, $taskFormValues)
    {
        if (isset($taskFormValues['uuid'])) {
            $task = $this->taskRepository->getTask($taskFormValues['uuid']);
            $task->label = $taskFormValues['label'];
            $task->taskContext = $taskFormValues['context'];
            $task->category = $taskFormValues['category'];
            $task->dateOfLastExposure = Date::parse($taskFormValues['dateOfLastExposure']);
            $task->communication = $taskFormValues['communication'];
            $this->taskRepository->updateTask($task);
        } else {
            $this->taskRepository->createTask($caseUuid,
                $taskFormValues['label'],
                $taskFormValues['context'],
                $taskFormValues['category'],
                Date::parse($taskFormValues['dateOfLastExposure']),
                $taskFormValues['communication']);
        }
    }

}

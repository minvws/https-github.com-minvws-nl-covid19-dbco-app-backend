<?php

namespace App\Services;

use App\Repositories\CaseRepository;
use App\Repositories\PairingRepository;
use App\Repositories\TaskRepository;
use App\Models\CovidCase;
use DateTime;
use Illuminate\Support\Collection;
use Jenssegers\Date\Date;

/**
 * Responsible for managing cases.
 *
 * @package App\Services
 */
class CaseService
{
    /**
     * @var CaseRepository
     */
    private CaseRepository $caseRepository;

    /**
     * @var TaskRepository
     */
    private TaskRepository $taskRepository;

    /**
     * @var PairingRepository
     */
    private PairingRepository $pairingRepository;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authService;

    /**
     * Constructor.
     *
     * @param CaseRepository        $caseRepository
     * @param TaskRepository        $taskRepository
     * @param PairingRepository     $pairingRepository
     * @param AuthenticationService $authService
     */
    public function __construct(CaseRepository $caseRepository, TaskRepository $taskRepository, PairingRepository $pairingRepository, AuthenticationService $authService)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->pairingRepository = $pairingRepository;
        $this->authService = $authService;
    }

    public function createDraftCase(): CovidCase
    {
        $owner = $this->authService->getAuthenticatedUser();
        return $this->caseRepository->createCase($owner, CovidCase::STATUS_DRAFT);
    }

    /**
     * Create pairing code for the given case.
     *
     * @param string $caseUuid
     *
     * @return string|null Formatted pairing code.
     */
    public function createPairingCodeForCase(string $caseUuid): ?string
    {
        $case = $this->getCase($caseUuid);
        if (!$this->canAccess($case)) {
            return null;
        }

        $expiresAt = new DateTime("+1 day"); // TODO: move to config and/or base on case data
        $code = $this->pairingRepository->getPairingCode($case->uuid, $expiresAt);

        // apply formatting for readability (TODO: move to view?)
        return implode('-', str_split($code, 3));
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

    public function openCase(CovidCase $case)
    {
        $case->status = CovidCase::STATUS_OPEN;
        $this->updateCase($case);
    }

    public function createOrUpdateTask($caseUuid, $taskFormValues)
    {
        if (isset($taskFormValues['uuid'])) {
            $task = $this->taskRepository->getTask($taskFormValues['uuid']);
            $task->label = $taskFormValues['label'];
            $task->taskContext = $taskFormValues['taskContext'];
            $task->category = $taskFormValues['category'];
            $task->dateOfLastExposure = isset($taskFormValues['dateOfLastExposure']) ? Date::parse($taskFormValues['dateOfLastExposure']) : null;
            $task->communication = $taskFormValues['communication'] ?? 'ggd'                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     ;
            $this->taskRepository->updateTask($task);
        } else {
            $this->taskRepository->createTask($caseUuid,
                $taskFormValues['label'],
                $taskFormValues['taskContext'],
                $taskFormValues['category'] ?? '3',
                $taskFormValues['communication'] ?? 'ggd',
                isset($taskFormValues['dateOfLastExposure']) ? Date::parse($taskFormValues['dateOfLastExposure']) : null,
                );
        }
    }

    /**
     * @param string $caseUuid case to clean up
     * @param array $keep array of task uuids to keep
     */
    public function deleteRemovedTasks(string $caseUuid, array $keep)
    {
        $this->taskRepository->deleteRemovedTasks($caseUuid, $keep);
    }

}

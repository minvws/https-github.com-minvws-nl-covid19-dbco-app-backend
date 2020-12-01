<?php

namespace App\Services;

use App\Models\Answer;
use App\Repositories\AnswerRepository;
use App\Repositories\CaseExportRepository;
use App\Repositories\CaseRepository;
use App\Repositories\PairingRepository;
use App\Repositories\TaskRepository;
use App\Models\CovidCase;
use DateTime;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * @var AnswerRepository
     */
    private AnswerRepository $answerRepository;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authService;

    private CaseExportRepository $caseExportRepository;

    /**
     * Constructor.
     *
     * @param CaseRepository $caseRepository
     * @param TaskRepository $taskRepository
     * @param PairingRepository $pairingRepository
     * @param AnswerRepository $answerRepository
     * @param AuthenticationService $authService
     * @param CaseExportRepository $caseExportRepository
     */
    public function __construct(CaseRepository $caseRepository,
                                TaskRepository $taskRepository,
                                PairingRepository $pairingRepository,
                                AnswerRepository $answerRepository,
                                AuthenticationService $authService,
                                CaseExportRepository $caseExportRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->pairingRepository = $pairingRepository;
        $this->answerRepository =$answerRepository;
        $this->authService = $authService;
        $this->caseExportRepository = $caseExportRepository;
    }

    public function createDraftCase(): CovidCase
    {
        $owner = $this->authService->getAuthenticatedUser();
        $assignedTo = null;
        if (!$this->authService->isPlanner()) {
            // Auto assign to yourself if you aren't a planner
            $assignedTo = $owner;
        }
        return $this->caseRepository->createCase($owner, CovidCase::STATUS_DRAFT, $assignedTo);
    }

    /**
     * Create pairing code for the given case.
     *
     * @param CovidCase $case
     *
     * @return string|null Formatted pairing code.
     */
    public function createPairingCodeForCase(CovidCase $case): ?string
    {
        if (!$this->canAccess($case)) {
            return null;
        }

        $expiresAt = Date::now()->addDays(1)->toDateTimeImmutable(); // TODO: move to config and/or base on case data
        $pairing = $this->pairingRepository->getPairing($case->uuid, $expiresAt);

        $this->caseRepository->setExpiry($case, $expiresAt, $pairing->expiresAt);

        // apply formatting for readability (TODO: move to view?)
        return implode('-', str_split($pairing->code, 3));
    }

    /**
     * @param $caseUuid
     * @param false $includeProgress If true, loads the progress of the case (significantly slower)
     * @return CovidCase|null
     */
    public function getCase($caseUuid, $includeProgress = false): ?CovidCase
    {
        $case = $this->caseRepository->getCase($caseUuid);
        if ($case) {
            $case->tasks = $this->taskRepository->getTasks($caseUuid)->all();

            if ($includeProgress) {
                $this->applyProgress($caseUuid, $case->tasks);
            }
        }
        return $case;
    }

    /**
     * @return LengthAwarePaginator
     */
    public function myCases(): LengthAwarePaginator
    {
        return $this->caseRepository->getCasesByAssignedUser($this->authService->getAuthenticatedUser());
    }

    public function organisationCases(): LengthAwarePaginator
    {
        return $this->caseRepository->getCasesByOrganisation($this->authService->getAuthenticatedUser());
    }

    /**
     * Check if the current user has access to a case
     * @param CovidCase $case The case to check
     * @return bool True if access is ok
     */
    public function canAccess(CovidCase $case): bool
    {
        $user = $this->authService->getAuthenticatedUser();
        return $user->uuid == $case->owner;
    }

    public function updateCase(CovidCase $case)
    {
        $this->caseRepository->updateCase($case);
    }

    public function exportCase(CovidCase $case): bool
    {
        return $this->caseExportRepository->export($case);
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
            $task->communication = $taskFormValues['communication'] ?? 'staff';
            $this->taskRepository->updateTask($task);
        } else {
            $this->taskRepository->createTask($caseUuid,
                $taskFormValues['label'],
                $taskFormValues['taskContext'],
                $taskFormValues['category'] ?? '3',
                $taskFormValues['communication'] ?? 'staff',
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

    private function applyProgress($caseUuid, &$tasks)
    {
        $tasksByTaskUuid = [];
        foreach ($tasks as $task) {
            $task->progress += ($task->dateOfLastExposure != null ? 25 : 0);
            $tasksByTaskUuid[$task->uuid] = $task;
        }

        $answers = $this->answerRepository->getAllAnswersByCase($caseUuid);
        foreach ($answers as $answer) {
            // Todo: this assumes a task's questionnaire has one ClassificationDetails object
            // and one ContactDetails object. This may not always be the case.
            $tasksByTaskUuid[$answer->taskUuid]->progress += $answer->progressContribution();
        }

        $tasks = array_values($tasksByTaskUuid);
    }

}

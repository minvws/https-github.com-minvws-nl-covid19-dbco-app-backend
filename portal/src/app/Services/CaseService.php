<?php

namespace App\Services;

use App\Models\Answer;
use App\Repositories\AnswerRepository;
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
     * @var AnswerRepository
     */
    private AnswerRepository $answerRepository;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authService;


    /**
     * Constructor.
     *
     * @param CaseRepository $caseRepository
     * @param TaskRepository $taskRepository
     * @param PairingRepository $pairingRepository
     * @param AnswerRepository $answerRepository
     * @param AuthenticationService $authService
     */
    public function __construct(CaseRepository $caseRepository,
                                TaskRepository $taskRepository,
                                PairingRepository $pairingRepository,
                                AnswerRepository $answerRepository,
                                AuthenticationService $authService)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->pairingRepository = $pairingRepository;
        $this->answerRepository =$answerRepository;
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

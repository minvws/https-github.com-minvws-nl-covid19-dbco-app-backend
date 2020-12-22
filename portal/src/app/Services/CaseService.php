<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Task;
use App\Repositories\AnswerRepository;
use App\Repositories\CaseUpdateNotificationRepository;
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

    private CaseUpdateNotificationRepository $caseExportRepository;

    /**
     * Constructor.
     *
     * @param CaseRepository $caseRepository
     * @param TaskRepository $taskRepository
     * @param PairingRepository $pairingRepository
     * @param AnswerRepository $answerRepository
     * @param AuthenticationService $authService
     * @param CaseUpdateNotificationRepository $caseExportRepository
     */
    public function __construct(CaseRepository $caseRepository,
                                TaskRepository $taskRepository,
                                PairingRepository $pairingRepository,
                                AnswerRepository $answerRepository,
                                AuthenticationService $authService,
                                CaseUpdateNotificationRepository $caseExportRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->pairingRepository = $pairingRepository;
        $this->answerRepository = $answerRepository;
        $this->authService = $authService;
        $this->caseExportRepository = $caseExportRepository;
    }

    public function createDraftCase(): CovidCase
    {
        $owner = $this->authService->getAuthenticatedUser();
        $assignedTo = null;
        if (!$this->authService->hasPlannerRole()) {
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
        return implode('-', str_split($pairing->code, 4));
    }

    /**
     * @param $caseUuid
     * @param false $includeProgress If true, loads the progress of the case (significantly slower)
     * @return CovidCase|null
     */
    public function getCase($caseUuid, $includeProgress = false): ?CovidCase
    {
        $case = $this->caseRepository->getCase($caseUuid);

        if ($case === null) {
            return null;
        }

        $case->tasks = $this->taskRepository->getTasks($caseUuid)->all();

        if ($includeProgress) {
            $this->applyProgress($case);
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

    public function notifyCaseUpdate(CovidCase $case): bool
    {
        return $this->caseExportRepository->notify($case);
    }

    public function openCase(CovidCase $case)
    {
        $case->status = CovidCase::STATUS_OPEN;
        $this->updateCase($case);
    }

    /**
     * @param $caseUuid
     * @param $taskFormValues
     * @return String The uuid of the newly credted record (or the updated one)
     */
    public function createOrUpdateTask($caseUuid, $taskFormValues): String
    {
        if (isset($taskFormValues['uuid'])) {
            $task = $this->taskRepository->getTask($taskFormValues['uuid']);
            $task->label = $taskFormValues['label'];
            $task->taskContext = $taskFormValues['taskContext'];
            $task->category = $taskFormValues['category'];
            $task->dateOfLastExposure = isset($taskFormValues['dateOfLastExposure']) ? Date::parse($taskFormValues['dateOfLastExposure']) : null;
            $task->communication = $taskFormValues['communication'] ?? 'staff';
            $this->taskRepository->updateTask($task);
            return $task->uuid;
        } else {
            $newTask = $this->taskRepository->createTask($caseUuid,
                $taskFormValues['label'],
                $taskFormValues['taskContext'],
                $taskFormValues['category'] ?? '3',
                $taskFormValues['communication'] ?? 'staff',
                isset($taskFormValues['dateOfLastExposure']) ? Date::parse($taskFormValues['dateOfLastExposure']) : null,
            );
            return $newTask->uuid;
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

    /**
     * Task completion progress is divided into three buckets to keep the UI simple:
     * - 'completed': all details are available, all questions answered
     * - 'contactable': we have enough basic data to contact the person
     * - 'other': too much is still missing, provide the user UI warnings
     *
     * @param CovidCase $case
     */
    private function applyProgress(CovidCase $case): void
    {
        foreach ($case->tasks as &$task) {
            $task->progress = Task::TASK_DATA_INCOMPLETE;

            if ($task->dateOfLastExposure === null) {
                // No last exposure date: keep incomplete and move to next
                continue;
            }

            // Check Task questionnaire answers for classification and contact details.
            // Any missed question will mark the Task partially-complete.
            $hasClassification = false;
            $hasContactDetails = false;
            $isComplete = true;
            $answers = $this->answerRepository->getAllAnswersByTask($task->uuid);

            foreach ($answers as $answer) {
                assert($answer instanceof Answer);

                // Classification and ContactDetails answers
                if ($answer instanceof ClassificationDetailsAnswer) {
                    $hasClassification = $answer->isContactable();
                } elseif ($answer instanceof ContactDetailsAnswer) {
                    $hasContactDetails = $answer->isContactable();
                }

                $isComplete = $isComplete && $answer->isCompleted();
            }

            if ($isComplete && $hasClassification && $hasContactDetails) {
                $task->progress = Task::TASK_DATA_COMPLETE;
            } elseif ($hasClassification && $hasContactDetails) {
                $task->progress = Task::TASK_DATA_CONTACTABLE;
            }

        }
    }

    public function getCopyDataCase(CovidCase $case)
    {
        return "Naam: " . $case->name . "\n"
            . "Case ID: " . $case->caseId;
    }

    public function getCopyDataIndex(CovidCase $case)
    {
        return "Datum eerste ziektedag (EZD): " . $case->dateOfSymptomOnset->format('d-m-Y') .
            "\nDatum start besmettelijke periode: " . $case->calculateContagiousPeriodStart()->format('d-m-Y');
    }

}

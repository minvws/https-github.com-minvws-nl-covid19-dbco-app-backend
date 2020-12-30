<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ContactDetailsAnswer;
use App\Models\CovidCase;
use App\Models\Question;
use App\Models\Task;
use App\Repositories\AnswerRepository;
use App\Repositories\CaseRepository;
use App\Repositories\CaseUpdateNotificationRepository;
use App\Repositories\PairingRepository;
use App\Repositories\StateRepository;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;
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

    private QuestionnaireService $questionnaireService;

    /**
     * @var StateRepository
     */
    private StateRepository $stateRepository;

    /**
     * Constructor.
     *
     * @param CaseRepository $caseRepository
     * @param TaskRepository $taskRepository
     * @param PairingRepository $pairingRepository
     * @param AnswerRepository $answerRepository
     * @param AuthenticationService $authService
     * @param CaseUpdateNotificationRepository $caseExportRepository
     * @param StateRepository $stateRepository
     */
    public function __construct(CaseRepository $caseRepository,
                                TaskRepository $taskRepository,
                                PairingRepository $pairingRepository,
                                AnswerRepository $answerRepository,
                                AuthenticationService $authService,
                                CaseUpdateNotificationRepository $caseExportRepository,
                                QuestionnaireService $questionnaireService,
                                StateRepository $stateRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
        $this->pairingRepository = $pairingRepository;
        $this->answerRepository = $answerRepository;
        $this->authService = $authService;
        $this->caseExportRepository = $caseExportRepository;
        $this->questionnaireService = $questionnaireService;
        $this->stateRepository = $stateRepository;
    }

    public function createDraftCase(): CovidCase
    {
        $owner = $this->authService->getAuthenticatedUser();
        $assignedTo = null;

        // Auto assign to yourself
        $assignedTo = $owner;

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
            if (isset($taskFormValues['label'])) {
                $task->label = $taskFormValues['label'];
            }
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
     * - 'incomplete': too much is still missing, provide the user UI warnings
     *
     * @param CovidCase $case
     */
    private function applyProgress(CovidCase $case): void
    {
        foreach ($case->tasks as &$task) {
            $task->progress = Task::TASK_DATA_INCOMPLETE;

            if (empty($task->category) || empty($task->dateOfLastExposure)) {
                // No classification or last exposure date: incomplete, move to next task
                continue;
            }

            // Check Task questionnaire answers for classification and contact details.
            $hasContactDetails = false;
            $answers = $this->answerRepository->getAllAnswersByTask($task->uuid);

            $answerIsCompleted = [];
            foreach ($answers as $answer) {
                /**
                 * @var Answer $answer
                 */
                $answerIsCompleted[$answer->questionUuid] = $answer->isCompleted();

                if ($answer instanceof ContactDetailsAnswer) {
                    $hasContactDetails = (!empty($answer->firstname) || !empty($answer->lastname)) && !empty($answer->phonenumber);
                }
            }

            if (!$hasContactDetails) {
                // No contact or classification data, skip the rest of the questionnaire
                continue;
            }
            $task->progress = Task::TASK_DATA_CONTACTABLE;

            // Any missed question will mark the Task partially-complete.
            $questionnaire = $this->questionnaireService->getQuestionnaire($task->questionnaireUuid);
            foreach ($questionnaire->questions as $question) {
                /**
                 * @var Question $question
                 */
                if (in_array($task->category, $question->relevantForCategories) && $answerIsCompleted[$question->uuid] === false) {
                    // One missed answer: move on to next task
                    break 2;
                }
            }

            // No relevant questions were skipped or unanswered: questionnaire complete!
            $task->progress = Task::TASK_DATA_COMPLETE;
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

    public function markAsCopied(CovidCase $case, ?Task $task, string $fieldName): void
    {
        $firstTime = $this->stateRepository->markFieldAsCopied($case->uuid, $task->uuid ?? null, $fieldName);
        if ($firstTime) {
            if ($task != null) {
                // Task level copy
                $task->copiedAt = Date::now();
                $this->taskRepository->updateTask($task);
            } else {
                $case->copiedAt = Date::now();
                $this->caseRepository->updateCase($case);
            }
        }
    }

    public function linkCaseToExport(CovidCase $case, string $exportId): void
    {
        $case->exportId = $exportId;
        $case->exportedAt = Date::now();
        $this->caseRepository->updateCase($case);
    }

    public function assignCase(CovidCase $case, string $assigneeUuid): bool
    {
        $case->assignedUuid = $assigneeUuid;
        return $this->caseRepository->updateCase($case);

    }

}

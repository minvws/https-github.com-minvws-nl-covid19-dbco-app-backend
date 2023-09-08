<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Question;
use App\Models\SimpleAnswer;
use App\Models\Task;
use App\Repositories\AnswerRepository;
use App\Repositories\Bsn\BsnException;
use App\Repositories\TaskRepository;
use App\Services\Bsn\BsnService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DBCO\Shared\Application\Metrics\Events\AbstractEvent;
use Exception;
use Illuminate\Support\Collection;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Metrics\Services\EventService;
use Psr\Log\LoggerInterface;

use function sprintf;

class TaskService
{
    private TaskRepository $taskRepository;
    private AnswerRepository $answerRepository;
    private QuestionnaireService $questionnaireService;
    private EventService $eventService;
    private BsnService $bsnService;
    private TaskFragmentService $taskFragmentService;
    private LoggerInterface $logger;

    public function __construct(
        TaskRepository $taskRepository,
        AnswerRepository $answerRepository,
        QuestionnaireService $questionnaireService,
        EventService $eventService,
        BsnService $bsnService,
        TaskFragmentService $taskFragmentService,
        LoggerInterface $logger,
    ) {
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
        $this->questionnaireService = $questionnaireService;
        $this->eventService = $eventService;
        $this->bsnService = $bsnService;
        $this->taskFragmentService = $taskFragmentService;
        $this->logger = $logger;
    }

    public function getTask(string $taskUuid): ?Task
    {
        $task = $this->taskRepository->getTask($taskUuid);
        if ($task === null) {
            return null;
        }

        /** @var Task\General $general */
        $general = $this->taskFragmentService->loadFragment($taskUuid, 'general');

        $task->email = $general->email;
        $task->firstname = $general->firstname;
        $task->lastname = $general->lastname;
        $task->telephone = $general->phone;

        return $task;
    }

    public function getTaskByUuid(string $taskUuid): ?EloquentTask
    {
        return $this->taskRepository->getTaskByUuid($taskUuid);
    }

    public function getTaskIncludingSoftDeletes(string $taskUuid): ?EloquentTask
    {
        return $this->taskRepository->getTaskIncludingSoftDeletes($taskUuid);
    }

    public function restoreTask(EloquentTask $task): void
    {
        $this->taskRepository->restoreTask($task);
    }

    public function getTasks(string $caseUuid, TaskGroup $group): array
    {
        return $this->taskRepository->getTasks($caseUuid, $group)->all();
    }

    public function getAllAnswersByTask(Task $task): Collection
    {
        return $this->answerRepository->getAllAnswersByTask($task->uuid);
    }

    public function getTaskQuestionnaireAndAnswers(Task $task): array
    {
        $questionnaire = $task->questionnaireUuid === null
            ? $this->questionnaireService->getLatestQuestionnaire($task->taskType)
            : $this->questionnaireService->getQuestionnaire($task->questionnaireUuid);

        $answers = $this->getAllAnswersByTask($task);

        $answersByQuestionUuid = [];
        foreach ($answers as $answer) {
            $answersByQuestionUuid[$answer->questionUuid] = $answer;
        }

        return [
            $questionnaire,
            $answersByQuestionUuid,
        ];
    }

    public function createTask(
        string $caseUuid,
        TaskGroup $group,
        string $label,
        ?string $context,
        ?string $nature,
        ?string $category,
        ?string $communication,
        ?CarbonInterface $dateOfLastExposure,
        bool $isSource,
    ): Task {
        $task = $this->taskRepository->createTask(
            $caseUuid,
            $group,
            $label,
            $context,
            $nature,
            $category,
            $communication,
            $dateOfLastExposure,
            $isSource,
        );
        $this->eventService->registerTaskMetrics(AbstractEvent::ACTOR_STAFF, $caseUuid, $task->uuid);
        return $task;
    }

    public function updateTask(Task $task): bool
    {
        $oldTaskData = $this->eventService->retrieveTaskData($task->uuid);

        $updatedTask = $this->taskRepository->updateTask($task);

        $this->eventService->registerTaskMetrics(AbstractEvent::ACTOR_STAFF, $task->caseUuid, $task->uuid, $oldTaskData);

        return $updatedTask;
    }

    public function deleteTask(EloquentTask $task): void
    {
        $this->taskRepository->deleteTask($task);
    }

    public function closeTask(Task $task): void
    {
        $task->status = Task::TASK_STATUS_CLOSED;

        $this->taskRepository->updateTask($task);
    }

    /**
     * @param array<Question> $questions
     * @param array<Answer> $answers
     */
    public function updateTaskAnswers(
        Task $task,
        array $questions,
        array $answers,
        array $formData,
        AuditEvent $auditEvent,
    ): void {
        $oldTaskData = $this->eventService->retrieveTaskData($task->uuid);
        // Update the Task questionnaire
        foreach ($questions as $question) {
            if (!isset($formData[$question->uuid])) {
                // No answer in returned form: ignore, do not create empty Answer
                continue;
            }

            // Special case: update the Task's last exposure date
            if ($question->questionType === 'lastcontactdate') {
                if ($task->dateOfLastExposure !== $formData['lastcontactdate']) {
                    $task->dateOfLastExposure = new CarbonImmutable($formData['lastcontactdate']);
                    $this->taskRepository->updateTask($task);
                }
                continue;
            }

            if (isset($answers[$question->uuid])) {
                // Update existing answer
                $answer = $answers[$question->uuid];
                $answer->fromFormValue($formData[$question->uuid]);
                $auditEvent->object(AuditObject::create("answer", $answer->uuid));
                $this->answerRepository->updateAnswer($answer);
            } else {
                // Create new Answer
                $answer = $this->createNewAnswerForQuestion($question, $formData[$question->uuid]);
                $answer->taskUuid = $task->uuid;
                $answer->questionUuid = $question->uuid;
                $dbAnswer = $this->answerRepository->createAnswer($answer);
                $auditEvent->object(AuditObject::create("answer", $dbAnswer->uuid));
            }
        }
        $this->eventService->registerTaskMetrics(AbstractEvent::ACTOR_STAFF, $task->caseUuid, $task->uuid, $oldTaskData);
    }

    /**
     * @throws Exception
     */
    private function createNewAnswerForQuestion(Question $question, array $formData = []): Answer
    {
        switch ($question->questionType) {
            case 'classificationdetails':
                $answer = new ClassificationDetailsAnswer();
                break;
            case 'contactdetails':
                $answer = new ContactDetailsAnswer();
                break;
            case 'date':
            case 'open':
            case 'multiplechoice':
                $answer = new SimpleAnswer();
                break;
            default:
                $errorMessage = sprintf('no Answer class for %s', $question->questionType);

                $this->logger->error($errorMessage);
                throw new Exception($errorMessage);
        }

        if (!empty($formData)) {
            $answer->fromFormValue($formData);
        }

        return $answer;
    }

    /**
     * @param array<Answer> $answers
     */
    public function deriveLabel(Task $task, array $answers): ?string
    {
        $label = $task->label;

        foreach ($answers as $answer) {
            if (!$answer instanceof ContactDetailsAnswer) {
                continue;
            }

            if ($answer->firstname && $answer->lastname) {
                $label = $answer->firstname . ' ' . $answer->lastname;
                break;
            }

            if ($answer->firstname) {
                $label = $answer->firstname;
                break;
            }
        }

        return $label;
    }

    /**
     * @throws BsnException
     * @throws Exception
     */
    public function updatePseudoBsn(Task $task, string $pseudoBsnGuid): void
    {
        $eloquentTask = $this->getTaskByUuid($task->uuid);
        if ($eloquentTask === null) {
            return;
        }

        $pseudoBsn = $this->bsnService->getByPseudoBsnGuid($pseudoBsnGuid, $eloquentTask->covidCase->organisation->external_id);

        $task->pseudoBsnGuid = $pseudoBsn->getGuid();
        $this->updateTask($task);

        /** @var Task\PersonalDetails $personalDetails */
        $personalDetails = $this->taskFragmentService->loadFragment($task->uuid, 'personalDetails');
        $personalDetails->bsnCensored = $pseudoBsn->getCensoredBsn();
        $personalDetails->bsnLetters = $pseudoBsn->getLetters();
        $this->taskFragmentService->storeFragment($task->uuid, 'personalDetails', $personalDetails);
    }

    public function countTaskGroupsForCase(EloquentCase $case): array
    {
        return $this->taskRepository->countTaskGroupsForCase($case);
    }
}

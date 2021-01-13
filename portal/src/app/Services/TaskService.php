<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\ContactDetailsAnswer;
use App\Models\CovidCase;
use App\Models\Question;
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
    private QuestionnaireService $questionnaireService;

    public function __construct(TaskRepository $taskRepository,
                                AnswerRepository $answerRepository,
                                CaseService $caseService,
                                QuestionnaireService $questionnaireService)
    {
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
        $this->caseService = $caseService;
        $this->questionnaireService = $questionnaireService;
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

    public function applyProgress(&$task)
    {
        $task->progress = Task::TASK_DATA_INCOMPLETE;

        if (empty($task->category) || empty($task->dateOfLastExposure)) {
            // No classification or last exposure date: incomplete, move to next task
            return;
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
            return;
        }
        $task->progress = Task::TASK_DATA_CONTACTABLE;

        // Any missed question will mark the Task partially-complete.
        $questionnaire = $this->questionnaireService->getQuestionnaire($task->questionnaireUuid);
        foreach ($questionnaire->questions as $question) {
            /**
             * @var Question $question
             */
            if (in_array($task->category, $question->relevantForCategories) &&
                (!isset($answerIsCompleted[$question->uuid]) || $answerIsCompleted[$question->uuid] === false)) {
                // One missed answer: move on to next task
                return;
            }
        }

        // No relevant questions were skipped or unanswered: questionnaire complete!
        $task->progress = Task::TASK_DATA_COMPLETE;
    }
}

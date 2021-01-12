<?php

namespace App\Services;

use App\Models\CovidCase;
use App\Models\Questionnaire;
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

    public function __construct(
        TaskRepository $taskRepository,
        AnswerRepository $answerRepository,
        CaseService $caseService,
        QuestionnaireService $questionnaireService
    ) {
        $this->taskRepository = $taskRepository;
        $this->answerRepository = $answerRepository;
        $this->caseService = $caseService;
        $this->questionnaireService = $questionnaireService;
    }

    public function getTask(string $taskUuid)
    {
        return $this->taskRepository->getTask($taskUuid);
    }

    public function getCaseByTask(Task $task)
    {
        return $this->caseService->getCase($task->caseUuid);
    }

    public function canAccess(Task $task)
    {
        $case = $this->getCaseByTask($task);

        if ($case === null) {
            return false;
        }

        return $this->caseService->canAccess($case);
    }

    public function linkTaskToExport(Task $task, string $exportId): void
    {
        $task->exportId = $exportId;
        $task->exportedAt = Date::now();
        $this->taskRepository->updateTask($task);
    }

    public function getAllAnswersByTask(Task $task): Collection
    {
        return $this->answerRepository->getAllAnswersByTask($task->uuid);
    }

    public function getTaskQuestionnaireAndAnswers(Task $task): array
    {
        $questionnaireUuid = $task->questionnaireUuid;
        if ($task->questionnaireUuid === null) {
            // Not yet filled by user, get the latest questionnaire.
            $questionnaire = $this->questionnaireService->getLatestQuestionnaire($task->taskType);
            $answers = [];
        } else {
            $questionnaire = $this->questionnaireService->getQuestionnaire($questionnaireUuid);
            $answers = $this->getAllAnswersByTask($task);
        }

        $answersByQuestionUuid = [];
        foreach ($answers as $answer) {
            $answersByQuestionUuid[$answer->questionUuid] = $answer;
        }

        return [
            $questionnaire,
            $answersByQuestionUuid
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Repositories\StateRepository;
use App\Services\CaseService;
use App\Services\QuestionnaireService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    private TaskService $taskService;
    private QuestionnaireService $questionnaireService;

    public function __construct(TaskService $taskService,
                                QuestionnaireService $questionnaireService)
    {
        $this->taskService = $taskService;
        $this->questionnaireService = $questionnaireService;
    }

    public function viewTaskQuestionnaire($taskUuid)
    {
        $task = $this->taskService->getTask($taskUuid);
        if ($this->taskService->canAccess($task)) {
            $questionnaireUuid = $task->questionnaireUuid;
            if ($task->questionnaireUuid === null) {
                // Not yet filled by user, get the latest questionnaire.
                $questionnaire = $this->questionnaireService->getLatestQuestionnaire($task->taskType);
            } else {
                $questionnaire = $this->questionnaireService->getQuestionnaire($questionnaireUuid);
            }

            $answers = $this->taskService->getAllAnswersByTask($taskUuid);
            $answersByQuestionUuid = [];
            foreach ($answers as $answer) {
                $answersByQuestionUuid[$answer->questionUuid] = $answer->toFormValue();
            }

            return view('taskquestionnaire', [
                'task' => (array)$task,
                'questions' => $questionnaire->questions,
                'answers' => $answersByQuestionUuid
            ]);
        }
        return "access denied";
    }
}

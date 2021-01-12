<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Question;
use App\Models\SimpleAnswer;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\QuestionnaireService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private QuestionnaireService $questionnaireService;

    public function __construct(TaskService $taskService,
                                TaskRepository $taskRepository,
                                QuestionnaireService $questionnaireService)
    {
        $this->taskService = $taskService;
        $this->taskRepository = $taskRepository;
        $this->questionnaireService = $questionnaireService;
    }

    public function linkTaskToExport(Request $request)
    {
        $exportId = trim($request->input('exportId'));
        if (empty($exportId)) {
            return response()->json(['error' => "Export ID is invalid"], Response::HTTP_BAD_REQUEST);
        }

        $taskUuid = $request->input('taskId');
        $task = $this->taskService->getTask($taskUuid);

        if ($task === null) {
            return response()->json(['error' => "Task $taskUuid is invalid"], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->taskService->canAccess($task)) {
            return response()->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $this->taskService->linkTaskToExport($task, $exportId);

        return response()->json(['success' => 'success'], Response::HTTP_OK);
    }

    public function viewTaskQuestionnaire(string $taskUuid)
    {
        $task = $this->taskService->getTask($taskUuid);
        if (!$this->taskService->canAccess($task)) {
            return "access denied";
        }

        list($questionnaire, $answers) = $this->taskService->getTaskQuestionnaireAndAnswers($task);

        return view('taskquestionnaire', [
            'task' => $task,
            'questions' => $questionnaire->questions,
            'answers' => array_map(fn(Answer $answer) => $answer->toFormValue(), $answers)
        ]);
    }

    public function saveTaskQuestionnaire(Request $request)
    {
        // Retrieve the Task
        $taskUuid = $request->route('taskUuid');

        $task = $this->taskService->getTask($taskUuid);
        if ($task === null || !$this->taskService->canAccess($task)) {
            return response('access denied', 403);
        }

        // Gather Task's relevant questions and existing answers
        list($questionnaire, $answers) = $this->taskService->getTaskQuestionnaireAndAnswers($task);

        $rules = [];
        foreach ($questionnaire->questions as $question) {
            /**
             * @var Question $question
             */
            if (!in_array($task->category, $question->relevantForCategories)) {
                continue;
            }

            $rules = array_merge($rules, $this->getQuestionFormValidationRules($question));
        }

        // Pull in the form data for the subset of questions and validate
        $validator = Validator::make($request->all(), $rules);
//        error_log(var_export([
//            "form" => $request->all(),
//            "rules" => $rules,
//            "data" => $validator->validated(),
//            "errors" => $validator->errors()
//        ], true));

        if (!$validator->fails()) {
            $this->updateTaskAnswers($answers, $validator->validated());

            // Close Task for further editing by Index
            $task->status = Task::TASK_STATUS_CLOSED;
            $this->taskRepository->updateTask($task);
        }

        // Return the rendered sidebar
        return view('taskquestionnaire', [
            'task' => $task,
            'questions' => $questionnaire->questions,
            'answers' => array_map(fn(Answer $answer) => $answer->toFormValue(), $answers),
            'errors' => $validator->errors()
        ]);
    }

    private function updateTaskAnswers(array $answers, array $formData): void
    {
        var_export($formData);
        var_export($answers);

        foreach ($answers as $answer) {
            /**
             * @var Answer $answer
             */
            if (isset($formData[$answer->questionUuid])) {
                $answer->fromFormValue($formData[$answer->questionUuid]);
            }
        }
    }

    private function getQuestionFormValidationRules(Question $question): array
    {
        // Default no validation rule: field will be ignored
        $rules = [];

        switch ($question->questionType) {
            case 'classificationdetails':
                $rules = ClassificationDetailsAnswer::getValidationRules();
                break;
            case 'contactdetails':
                $rules = ContactDetailsAnswer::getValidationRules();
                break;
            case 'date':
                $rules = SimpleAnswer::getValidationRules();
                break;
            case 'multiplechoice':
                break;
            default:
                error_log("no validation for {$question->questionType}");
        }

        // The form fields have the question.uuid prefixed
        $formRules = [];
        foreach ($rules as $fieldName => $rule) {
            $formFieldName = sprintf('%s.%s', $question->uuid, $fieldName);
            $formRules[$formFieldName] = $rule;
        }

        return $formRules;
    }
}

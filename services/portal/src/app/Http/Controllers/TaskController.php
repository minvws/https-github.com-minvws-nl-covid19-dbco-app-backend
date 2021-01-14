<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\SimpleAnswer;
use App\Models\Task;
use App\Repositories\AnswerRepository;
use App\Repositories\TaskRepository;
use App\Services\QuestionnaireService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Date\Date;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private QuestionnaireService $questionnaireService;
    private AnswerRepository $answerRepository;

    public function __construct(
        TaskService $taskService,
        TaskRepository $taskRepository,
        QuestionnaireService $questionnaireService,
        AnswerRepository $answerRepository)
    {
        $this->taskService = $taskService;
        $this->taskRepository = $taskRepository;
        $this->questionnaireService = $questionnaireService;
        $this->answerRepository = $answerRepository;
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
        $questions = $this->addLastExposureDateQuestion($questionnaire->questions);

        return view('taskquestionnaire', [
            'task' => $task,
            'questions' => $questions,
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

        // Gather Task's relevant questionnaire and existing answers
        list($questionnaire, $answers) = $this->taskService->getTaskQuestionnaireAndAnswers($task);

        if ($task->questionnaireUuid === null) {
            $task->questionnaireUuid = $questionnaire->uuid;
            $this->taskRepository->updateTask($task);
        }

        // Collect relevant questions and validation rules for this questionnaire
        $relevantQuestions = [];
        $rules = [];
        foreach ($questionnaire->questions as $question) {
            /**
             * @var Question $question
             */
            if (!in_array($task->category, $question->relevantForCategories)) {
                continue;
            }

            $relevantQuestions[] = $question;
            $rules = array_merge($rules, $this->getQuestionFormValidationRules($question));
        }

        // Special case: add last exposure date as the second question
        $rules['lastcontactdate'] = 'nullable|date';
        $relevantQuestions = $this->addLastExposureDateQuestion($relevantQuestions);

        // Pull in the form data for the subset of questions and validate
        $validator = Validator::make($request->all(), $rules);
        if (!$validator->fails()) {
            // Update the questionnaire
            $this->updateTaskAnswers($task, $relevantQuestions, $answers, $validator->validated());

            // Close Task for further editing by Index
            $task->status = Task::TASK_STATUS_CLOSED;
            $this->taskRepository->updateTask($task);
        }

        // Return the rendered sidebar
        return view('taskquestionnaire', [
            'task' => $task,
            'questions' => $relevantQuestions,
            'answers' => array_map(fn(Answer $answer) => $answer->toFormValue(), $answers),
        ])->withErrors($validator);
    }

    /**
     * @param Question[] $questions
     * @return Question[]
     */
    private function addLastExposureDateQuestion(array $questions): array
    {
        $_q = new Question;
        $_q->uuid = 'lastcontactdate';
        $_q->questionType = 'lastcontactdate';
        $_q->relevantForCategories = ['1', '2a', '2b', '3'];

        array_splice($questions, 1, 0, [$_q]);
        return $questions;
    }

    /**
     * @param Task $task
     * @param Question[] $questions
     * @param Answer[] $answers
     * @param array $formData
     */
    private function updateTaskAnswers(Task $task, array $questions, array $answers, array $formData): void
    {
        // Update the Task questionnaire
        foreach ($questions as $question) {
           if (!isset($formData[$question->uuid])) {
               // No answer in returned form: ignore, do not create empty Answer
               continue;
           }

            // Special case: update the Task's last exposure date
            if ($question->questionType === 'lastcontactdate') {
               if ($task->dateOfLastExposure !== $formData['lastcontactdate']) {
                   $task->dateOfLastExposure = new Date($formData['lastcontactdate']);
                   $this->taskRepository->updateTask($task);
               }
               continue;
           }

           if (isset($answers[$question->uuid])) {
               error_log("update: question={$question->label} answer={$answers[$question->uuid]->uuid} with " . var_export($formData[$question->uuid], true));
               // Update existing answer
                $answer = $answers[$question->uuid];
                $answer->fromFormValue($formData[$question->uuid]);
                $this->answerRepository->updateAnswer($answer);
           } else {
               error_log("insert: question={$question->label}  with " . var_export($formData[$question->uuid], true));
               // Create new Answer
               $answer = $this->createNewAnswerForQuestion($question, $formData[$question->uuid]);
               $answer->taskUuid = $task->uuid;
               $answer->questionUuid = $question->uuid;
               $this->answerRepository->createAnswer($answer);

           }
        }
    }

    // @todo centralize Question/Answer helpers
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
                $rules = SimpleAnswer::getValidationRules();
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

    // @todo centralize Question/Answer helpers
    private function createNewAnswerForQuestion(Question $question, array $formData = []): Answer
    {
        switch ($question->questionType) {
            case 'classificationdetails':
                $answer = new ClassificationDetailsAnswer;
                break;
            case 'contactdetails':
                $answer = new ContactDetailsAnswer();
                break;
            case 'date':
                $answer = new SimpleAnswer();
                break;
            case 'multiplechoice':
                $answer = new SimpleAnswer();
                break;
            default:
                error_log("no Answer class for {$question->questionType}");
        }

        if (!empty($formData)) {
            $answer->fromFormValue($formData);
        }

        return $answer;
    }
}

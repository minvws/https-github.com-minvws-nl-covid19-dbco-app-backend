<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Api\ApiRequest;
use App\Models\ContactDetailsAnswer;
use App\Models\Eloquent\EloquentTask;
use App\Models\Question;
use App\Models\SimpleAnswer;
use App\Services\TaskService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use Webmozart\Assert\Assert;

use function array_merge;
use function error_log;
use function in_array;
use function sprintf;
use function view;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(
        TaskService $taskService,
    ) {
        $this->taskService = $taskService;
    }

    public function saveTaskQuestionnaire(ApiRequest $request, EloquentTask $eloquentTask, AuditEvent $auditEvent): View
    {
        // Retrieve the Task
        $task = $this->taskService->getTask($eloquentTask->uuid);
        Assert::notNull($task);

        $auditEvent->object(AuditObject::create("task", $task->uuid));

        // Save the Dossier Number on task level
        if ($request->has('ggd_dossier_number')) {
            $task->dossierNumber = $request->getStringOrNull('ggd_dossier_number');
            $this->taskService->updateTask($task);
        }

        // Gather Task's relevant questionnaire and existing answers
        [$questionnaire, $answers] = $this->taskService->getTaskQuestionnaireAndAnswers($task);

        if ($task->questionnaireUuid === null) {
            $task->questionnaireUuid = $questionnaire->uuid;
            $this->taskService->updateTask($task);
        }

        // Collect relevant questions and validation rules for this questionnaire
        $relevantQuestions = [];
        $rules = [];
        foreach ($questionnaire->questions as $question) {
            /**
             * @var Question $question
             */
            if (!in_array($task->category, $question->relevantForCategories, true)) {
                continue;
            }

            $relevantQuestions[] = $question;
            $rules = array_merge($rules, $this->getQuestionFormValidationRules($question));
        }

        // Special case: add last exposure date as the second question
        $rules['lastcontactdate'] = 'nullable|date';
        $relevantQuestions = $this->filterQuestionsForSidebar($relevantQuestions);

        // Pull in the form data for the subset of questions and validate
        $validator = Validator::make($request->all(), $rules);
        if (!$validator->fails()) {
            // Update the questionnaire
            $this->taskService->updateTaskAnswers($task, $relevantQuestions, $answers, $validator->validated(), $auditEvent);

            // Update the task label based on ContactDetailsAnswer
            $task->derivedLabel = $this->taskService->deriveLabel($task, $answers);

            // Close Task for further editing by Index
            $this->taskService->closeTask($task);
        }

        // Populate sidebar answers with user input
        $formAnswers = [];
        foreach ($relevantQuestions as $question) {
            $formAnswers[$question->uuid] = $request->getString($question->uuid);
        }

        // Return the rendered sidebar
        return view('taskquestionnaire', [
            'task' => $task,
            'questions' => $relevantQuestions,
            'answers' => $formAnswers,
        ])->withErrors($validator);
    }

    /**
     * @param array<Question> $questions
     *
     * @return array<Question>
     */
    private function filterQuestionsForSidebar(array $questions): array
    {
        $filteredQuestions = [];
        foreach ($questions as $question) {
            // The classification details object sets the task.category, but we already
            // edit that field inline in the table on the left of the sidebar, so we don't
            // want to display it twice.
            if ($question->questionType !== 'classificationdetails') {
                $filteredQuestions[] = $question;
            }
        }
        return $filteredQuestions;
    }

    private function getQuestionFormValidationRules(Question $question): array
    {
        // Default no validation rule: field will be ignored
        $rules = [];

        switch ($question->questionType) {
            case 'contactdetails':
                $rules = ContactDetailsAnswer::getValidationRules();
                break;
            case 'date':
            case 'multiplechoice':
            case 'open':
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
}

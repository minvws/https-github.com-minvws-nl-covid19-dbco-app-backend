<?php

namespace App\Http\Controllers\Api;

use App\Models\Answer;
use App\Models\ContactDetailsAnswer;
use App\Models\Question;
use App\Models\Task;
use App\Services\CaseService;
use App\Services\QuestionnaireService;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Jenssegers\Date\Date;

class ApiTaskController extends ApiController
{
    private CaseService $caseService;
    private TaskService $taskService;
    private QuestionnaireService $questionnaireService;

    public function __construct(
        TaskService $taskService,
        CaseService $caseService,
        QuestionnaireService $questionnaireService
    )
    {
        $this->taskService = $taskService;
        $this->caseService = $caseService;
        $this->questionnaireService = $questionnaireService;
    }

    public function getCaseTasks($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid, false);

        if ($case === null) {
            return response()->json(['error' => "Deze case bestaat niet (meer)"], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->caseService->canAccess($case) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de case'], Response::HTTP_FORBIDDEN);
        }

        $tasks = $this->taskService->getTasks($caseUuid);

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Task completion progress is divided into three buckets to keep the UI simple:
     * - 'completed': all details are available, all questions answered
     * - 'contactable': we have enough basic data to contact the person
     * - 'incomplete': too much is still missing, provide the user UI warnings
     *
     * @param array $tasks
     */
    private function applyProgress(array $tasks): void
    {
        foreach ($tasks as &$task) {
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
                if (in_array($task->category, $question->relevantForCategories) &&
                    (!isset($answerIsCompleted[$question->uuid]) || $answerIsCompleted[$question->uuid] === false)) {
                    // One missed answer: move on to next task
                    break 2;
                }
            }

            // No relevant questions were skipped or unanswered: questionnaire complete!
            $task->progress = Task::TASK_DATA_COMPLETE;
        }
    }

    public function updateTask(Request $request, $taskUuid)
    {
        $task = $this->taskService->getTask($taskUuid);

        if ($task === null) {
            return response()->json(['error' => "Deze taak bestaat niet (meer)"], Response::HTTP_NOT_FOUND);
        }
        if (!$this->taskService->canAccess($task) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de taak'], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validate([
            'task.uuid' => 'required',
            'task.label' => 'nullable', // can be null if derivedLabel was set
            'task.taskContext' => 'nullable',
            'task.category' => 'nullable',
            'task.communication' => 'nullable',
            'task.dateOfLastExposure' => 'nullable'
        ]);

        if (isset($validatedData['task']['label'])) {
            $task->label = $validatedData['task']['label'];
        }
        $task->taskContext = $validatedData['task']['taskContext'] ?? null;
        $task->category = $validatedData['task']['category'] ?? null;
        $task->dateOfLastExposure = isset($validatedData['task']['dateOfLastExposure']) ? Date::parse($validatedData['task']['dateOfLastExposure']) : null;
        $task->communication = $validatedData['task']['communication'] ?? 'staff';
        $this->taskService->updateTask($task);

        return response()->json(['task' => $task]);
    }

    public function createTask(Request $request, $caseUuid) {
        $case = $this->caseService->getCase($caseUuid, false);

        if ($case === null) {
            return response()->json(['error' => "Deze case bestaat niet (meer)"], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->caseService->canAccess($case) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de case'], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validate([
            'task.uuid' => 'nullable',
            'task.label' => 'required',
            'task.taskContext' => 'nullable',
            'task.category' => 'nullable',
            'task.communication' => 'nullable',
            'task.dateOfLastExposure' => 'nullable'
        ]);

        $newTask = $this->taskService->createTask($caseUuid,
            $validatedData['task']['label'],
            $validatedData['task']['taskContext'] ?? null,
            $validatedData['task']['category'] ?? '3',
            $validatedData['task']['communication'] ?? 'staff',
            isset($validatedData['task']['dateOfLastExposure']) ? Date::parse($validatedData['task']['dateOfLastExposure']) : null
        );

        return response()->json(['task' => $newTask]);
    }

    public function deleteTask($taskUuid) {
        $task = $this->taskService->getTask($taskUuid);

        if ($task === null) {
            return response()->json(['error' => "Deze taak bestaat niet (meer)"], Response::HTTP_NOT_FOUND);
        }
        if (!$this->taskService->canAccess($task) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de taak'], Response::HTTP_FORBIDDEN);
        }

        $this->taskService->deleteTask($task);

        return response()->json(['task' => null]);
    }

}

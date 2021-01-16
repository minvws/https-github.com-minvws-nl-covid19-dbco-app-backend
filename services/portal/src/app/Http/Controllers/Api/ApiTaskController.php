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

    public function getCaseTasks($caseUuid, Request $request)
    {
        $case = $this->caseService->getCase($caseUuid, false);

        $includeProgress = $request->input('includeProgress', false);

        if ($case === null) {
            return response()->json(['error' => "Deze case bestaat niet (meer)"], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->caseService->canAccess($case) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de case'], Response::HTTP_FORBIDDEN);
        }

        $tasks = $this->taskService->getTasks($caseUuid);

        if ($includeProgress) {
            $this->applyProgress($tasks);
        }

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
            $this->taskService->applyProgress($task);
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

}

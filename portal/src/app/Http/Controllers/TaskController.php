<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function markUpload(Request $request)
    {
        $remoteId = trim($request->input('remoteId'));
        if (empty($remoteId)) {
            return response()->json(['error' => "Hpzone ID is invalid"], Response::HTTP_BAD_REQUEST);
        }

        $taskUuid = $request->input('taskId');
        $task = $this->taskService->getTask($taskUuid);

        if ($task === null) {
            return response()->json(['error' => "Task $taskUuid is invalid"], Response::HTTP_BAD_REQUEST);
        }

        $this->taskService->linkTaskToHpzone($task, $remoteId);

        return response()->json(['success' => 'success'], Response::HTTP_OK);
    }
}

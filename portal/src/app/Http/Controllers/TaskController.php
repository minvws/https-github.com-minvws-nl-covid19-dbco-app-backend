<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function markUpload(Request $request)
    {
        $taskId = $request->input('taskId');
        $remoteId = $request->input('remoteId');

        return response()->json(['success' => 'success'], 200);
    }
}

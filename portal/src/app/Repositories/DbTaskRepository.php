<?php

namespace App\Repositories;

use App\Models\Eloquent\EloquentTask;
use App\Models\Task;
use Illuminate\Support\Collection;
use Jenssegers\Date\Date;
use Monolog\DateTimeImmutable;

class DbTaskRepository implements TaskRepository
{
    /**
     * Returns task list.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return Collection List of tasks
     */
    public function getTasks(string $caseUuid): Collection
    {
        $dbTasks = EloquentTask::where('case_uuid', $caseUuid)->orderBy('communication', 'asc')
                                                              ->orderBy('created_at', 'desc')->get();

        $tasks = array();

        foreach($dbTasks as $dbTask) {
            $tasks[] = $this->taskFromEloquentModel($dbTask);
        };

        return collect($tasks);
    }

    /**
     * Returns single task.
     *
     * @param string $uuid Task identifier.
     *
     * @return Task The task (or null if not found)
     */
    public function getTask(string $taskUuid): Task
    {
        $dbTask = $this->getTaskFromDb($taskUuid);
        return $dbTask != null ? $this->taskFromEloquentModel($dbTask): null;
    }

    private function getTaskFromDb(string $taskUuid): EloquentTask
    {
        $tasks = EloquentTask::where('uuid', $taskUuid)->get();
        return $tasks->first();
    }

    /**
     * Update case.
     *
     * @param Task $task Task to update
     */
    public function updateTask(Task $task)
    {
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from a Task.
        $dbTask = $this->getTaskFromDb($task->uuid);
        $dbTask->label = $task->label;
        $dbTask->task_context = $task->taskContext;
        $dbTask->communication = $task->communication;
        $dbTask->category = $task->category;
        $dbTask->date_of_last_exposure = $task->dateOfLastExposure != null? $task->dateOfLastExposure->toDateTimeImmutable() : null;
        $dbTask->save();
    }

    /**
     * Create a new task
     *
     * @return Task
     */
    public function createTask(string $caseUuid, string $label, string $context, string $category, Date $dateOfLastExposure, string $communication): Task
    {
        $dbTask = new EloquentTask();

        $dbTask->case_uuid = $caseUuid;
        $dbTask->label = $label;
        $dbTask->task_context = $context;
        $dbTask->category = $category;
        $dbTask->date_of_last_exposure = $dateOfLastExposure->toDateTimeImmutable();
        $dbTask->communication = $communication;
        $dbTask->source = 'portal';
        $dbTask->task_type = 'contact';
        $dbTask->informed_by_index = false;

        $dbTask->save();
        return $this->taskFromEloquentModel($dbTask);
    }

    private function taskFromEloquentModel(EloquentTask $dbTask): Task
    {
        $task = new Task();
        $task->uuid = $dbTask->uuid;
        $task->category = $dbTask->category;
        $task->communication = $dbTask->communication;
        $task->dateOfLastExposure = $dbTask->date_of_last_exposure != NULL ? new Date($dbTask->date_of_last_exposure) : null;
        $task->informedByIndex = $dbTask->informed_by_index;
        $task->label = $dbTask->label;
        $task->nature = $dbTask->nature;
        $task->source = $dbTask->source;
        $task->taskContext = $dbTask->task_context;
        $task->taskType = $dbTask->task_type;
        $task->questionnaireUuid = $dbTask->questionnaire_uuid;

        return $task;
    }

}

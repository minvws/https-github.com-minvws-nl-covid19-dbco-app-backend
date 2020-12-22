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
    public function getTask(string $taskUuid): ?Task
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
        $dbTask->date_of_last_exposure = $task->dateOfLastExposure !== null ? $task->dateOfLastExposure->toDateTimeImmutable() : null;
        $dbTask->export_id = $task->exportId;
        $dbTask->created_at = $task->createdAt;
        $dbTask->updated_at = $task->updatedAt;
        $dbTask->exported_at = $task->exportedAt !== null ? $task->exportedAt->toDateTimeImmutable() : null;
        $dbTask->copied_at = $task->copiedAt !== null ? $task->copiedAt->toDateTimeImmutable() : null;

        $dbTask->save();
    }

    /**
     * Create a new task
     *
     * @return Task
     */
    public function createTask(
        string $caseUuid,
        string $label,
        ?string $context,
        string $category,
        string $communication,
        ?Date $dateOfLastExposure
    ): Task {
        $dbTask = new EloquentTask();

        $dbTask->case_uuid = $caseUuid;
        $dbTask->label = $label;
        $dbTask->task_context = $context;
        $dbTask->category = $category;
        $dbTask->date_of_last_exposure = ($dateOfLastExposure !== null ? $dateOfLastExposure->toDateTimeImmutable() : null);
        $dbTask->communication = $communication;
        $dbTask->source = 'portal';
        $dbTask->task_type = 'contact';
        $dbTask->informed_by_index = 0;

        $dbTask->save();
        return $this->taskFromEloquentModel($dbTask);
    }

    private function taskFromEloquentModel(EloquentTask $dbTask): Task
    {
        $task = new Task();
        $task->uuid = $dbTask->uuid;
        $task->caseUuid = $dbTask->case_uuid;
        $task->category = $dbTask->category;
        $task->communication = $dbTask->communication;
        $task->dateOfLastExposure = $dbTask->date_of_last_exposure !== NULL ? new Date($dbTask->date_of_last_exposure) : null;
        $task->informedByIndex = $dbTask->informed_by_index === 1;
        $task->exportedAt = $dbTask->exported_at !== null ? new Date($dbTask->exported_at) : null;
        $task->copiedAt = $dbTask->copied_at !== null ? new Date($dbTask->copied_at) : null;
        $task->label = $dbTask->label;
        $task->nature = $dbTask->nature;
        $task->source = $dbTask->source;
        $task->taskContext = $dbTask->task_context;
        $task->taskType = $dbTask->task_type;
        $task->questionnaireUuid = $dbTask->questionnaire_uuid;
        $task->exportId = $dbTask->export_id;
        $task->createdAt = $dbTask->created_at !== null ? new Date($dbTask->created_at) : null;
        $task->updatedAt = $dbTask->updated_at !== null ? new Date($dbTask->updated_at) : null;

        return $task;
    }

    public function deleteRemovedTasks(string $caseUuid, array $keep)
    {
        EloquentTask::where('case_uuid', $caseUuid)->whereNotIn('uuid', $keep)->delete();
    }

}

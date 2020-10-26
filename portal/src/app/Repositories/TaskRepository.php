<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;
use Jenssegers\Date\Date;

interface TaskRepository
{
    /**
     * Returns task list.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return Collection List of tasks
     */
    public function getTasks(string $caseUuid): Collection;

    /**
     * Returns single task.
     *
     * @param string $uuid Task identifier.
     *
     * @return Task The task (or null if not found)
     */
    public function getTask(string $taskUuid): Task;

    /**
     * Create a new task
     *
     * @return Task
     */
    public function createTask(string $caseUuid, string $label, string $context, string $category, Date $dateOfLastExposure, string $communication): Task;
}

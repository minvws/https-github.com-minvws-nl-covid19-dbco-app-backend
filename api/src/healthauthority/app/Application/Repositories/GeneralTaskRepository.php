<?php
namespace App\Application\Repositories;

use App\Application\Models\TaskList;

/**
 * Used for retrieving general tasks.
 *
 * @package App\Application\Repositories
 */
interface GeneralTaskRepository
{
    /**
     * Returns the general task list.
     *
     * @return TaskList
     */
    public function getGeneralTasks(): TaskList;
}

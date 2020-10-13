<?php
namespace App\Application\Repositories;

use App\Application\Models\TaskList;

/**
 * Used for retrieving general tasks.
 *
 * Stub implementation.
 *
 * @package App\Application\Repositories
 */
class StubGeneralTaskRepository implements GeneralTaskRepository
{
    /**
     * Returns the general task list.
     *
     * @return TaskList
     */
    public function getGeneralTasks(): TaskList
    {
        $list = new TaskList();
        return $list;
    }
}

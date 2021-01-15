<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DBCO\HealthAuthorityAPI\Application\Models\TaskList;

/**
 * Used for retrieving general tasks.
 *
 * Stub implementation.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
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

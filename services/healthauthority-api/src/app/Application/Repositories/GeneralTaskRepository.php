<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DBCO\HealthAuthorityAPI\Application\Models\TaskList;

/**
 * Used for retrieving general tasks.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
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

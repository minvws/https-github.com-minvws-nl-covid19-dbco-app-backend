<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\GeneralTaskList;

/**
 * Used for retrieving general tasks.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface GeneralTaskRepository
{
    /**
     * Returns the general task list.
     *
     * @return GeneralTaskList
     */
    public function getGeneralTasks(): GeneralTaskList;
}

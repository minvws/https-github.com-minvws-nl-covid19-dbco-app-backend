<?php
namespace App\Application\Repositories;

use App\Application\Models\GeneralTaskList;

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
     * @return GeneralTaskList
     */
    public function getGeneralTasks(): GeneralTaskList;
}

<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Models\GeneralTaskList;
use Exception;

/**
 * Store the general task list in the cache.
 *
 * @package App\Application\Repositories
 */
interface GeneralTaskCacheRepository
{
    /**
     * Store the general task list in the cache.
     *
     * @param GeneralTaskList $tasks
     *
     * @throws Exception
     */
    public function putGeneralTasks(GeneralTaskList $tasks): void;
}


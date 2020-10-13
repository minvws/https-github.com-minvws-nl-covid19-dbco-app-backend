<?php
namespace App\Application\Repositories;

use App\Application\Models\GeneralTaskList;

/**
 * Used for retrieving general and case specific tasks.
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
     * @return GeneralTaskList
     */
    public function getGeneralTasks(): GeneralTaskList
    {
        $body = <<<'EOD'
{
  "tasks": []
}
EOD;

        return new GeneralTaskList([], $body);
    }
}

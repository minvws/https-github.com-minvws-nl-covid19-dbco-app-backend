<?php
namespace App\Application\Repositories;

use App\Application\Models\CaseTaskList;

/**
 * Used for retrieving case specific tasks.
 *
 * @package App\Application\Repositories
 */
interface CaseTaskRepository
{
    /**
     * Returns the case task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CaseTaskList
     */
    public function getCaseTasks(string $caseId): CaseTaskList;

    /**
     * Submit case tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCaseTasks(string $caseId, string $body): void;
}

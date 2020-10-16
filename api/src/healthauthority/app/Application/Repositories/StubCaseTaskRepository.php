<?php
namespace App\Application\Repositories;

use App\Application\Models\Task;
use App\Application\Models\TaskList;

/**
 * Used for retrieving case specific tasks.
 *
 * Stub implementation.
 *
 * @package App\Application\Repositories
 */
class StubCaseTaskRepository implements CaseTaskRepository
{
    /**
     * Returns the case task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return TaskList
     */
    public function getCaseTasks(string $caseId): Infection
    {
        $infection = new Infection();

        $task1 = new Task();
        $task1->uuid = "123e4567-e89b-12d3-a456-426614172000";
        $task1->taskType = "contact";
        $task1->source = "portal";
        $task1->label = "Lia B";
        $task1->context = "Partner";
        $task1->category = "1";
        $task1->communication = "index";
        $task1->dateOfLastExposure = "2020-10-13";

        $infection->tasks[] = $task1;
        $infection->dateOfSymptomOnset = "2020-10-14";

        return $infection;
    }

    /**
     * Submit case tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCaseTasks(string $caseId, string $body): void
    {

    }
}

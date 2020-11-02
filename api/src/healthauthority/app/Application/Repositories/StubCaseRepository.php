<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeImmutable;
use DBCO\HealthAuthorityAPI\Application\Models\Task;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;

/**
 * Used for retrieving case specific tasks.
 *
 * Stub implementation.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class StubCaseRepository implements CaseRepository
{
    /**
     * Returns the case with its task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CovidCase
     */
    public function getCase(string $caseId): CovidCase
    {
        $case = new CovidCase();

        $task1 = new Task();
        $task1->uuid = "123e4567-e89b-12d3-a456-426614172000";
        $task1->taskType = "contact";
        $task1->source = "portal";
        $task1->label = "Lia B";
        $task1->context = "Partner";
        $task1->category = "1";
        $task1->communication = "index";
        $task1->dateOfLastExposure = new DateTimeImmutable("2020-10-13");

        $case->tasks[] = $task1;
        $case->dateOfSymptomOnset = new DateTimeImmutable("2020-10-14");

        return $case;
    }

    /**
     * Submit case tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCase(string $caseId, string $body): void
    {

    }
}

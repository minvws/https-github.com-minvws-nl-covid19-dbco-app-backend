<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeImmutable;
use DBCO\HealthAuthorityAPI\Application\Models\Task;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;
use PDO;

/**
 * Used for retrieving case specific tasks from the database.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class DbCaseRepository implements CaseRepository
{
    /**
     * @var PDO
     */
    private PDO $client;

    /**
     * Constructor.
     *
     * @param PDO $client
     */
    public function __construct(PDO $client)
    {
        $this->client = $client;
    }

    /**
     * Returns the case with its task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CovidCase|null
     */
    public function getCase(string $caseId): ?CovidCase
    {
        $stmt = $this->client->prepare("
            SELECT c.date_of_symptom_onset
            FROM covidcase c
            WHERE c.uuid = :caseId
            AND c.status = 'open'
        ");

        $stmt->execute([ 'caseId' => $caseId ]);

        $dateOfSymptomOnsetStr = $stmt->fetchColumn();
        if ($dateOfSymptomOnsetStr === false) {
            return null;
        }

        $case = new CovidCase();
        $case->dateOfSymptomOnset =
            $dateOfSymptomOnsetStr != null ? new DateTimeImmutable($dateOfSymptomOnsetStr) : null;

        $stmt = $this->client->prepare("
            SELECT t.*
            FROM task t
            WHERE t.case_uuid = :caseId
        ");

        $stmt->execute([ 'caseId' => $caseId ]);

        while ($row = $stmt->fetchObject()) {
            $task = new Task();
            $task->uuid = $row->uuid;
            $task->taskType = $row->task_type;
            $task->source = $row->source;
            $task->label = $row->label;
            $task->context = $row->task_context;
            $task->category = $row->category;
            $task->communication = $row->communication;
            $task->dateOfLastExposure =
                $row->date_of_last_exposure != null ? new DateTimeImmutable($row->date_of_last_exposure) : null;

            $case->tasks[] = $task;
        }

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

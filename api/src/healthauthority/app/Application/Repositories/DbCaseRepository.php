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
     * Check if a case exists.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return bool
     */
    public function caseExists(string $caseUuid): bool
    {
        $stmt = $this->client->prepare("
            SELECT COUNT(1) as c
            FROM covidcase c
            WHERE c.uuid = :caseUuid
            AND c.status = 'open'
        ");

        $stmt->execute([ 'caseUuid' => $caseUuid ]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    /**
     * Returns the case with its task list.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return CovidCase|null
     */
    public function getCase(string $caseUuid): ?CovidCase
    {
        $stmt = $this->client->prepare("
            SELECT c.uuid, c.date_of_symptom_onset
            FROM covidcase c
            WHERE c.uuid = :caseUuid
            AND c.status = 'open'
        ");

        $stmt->execute([ 'caseUuid' => $caseUuid ]);

        $row = $stmt->fetchObject();
        if ($row === false) {
            return null;
        }

        $case = new CovidCase();
        $case->uuid = $row->uuid;
        $case->dateOfSymptomOnset =
            !empty($row->date_of_symptom_onset) ? new DateTimeImmutable($row->date_of_symptom_onset) : null;

        $stmt = $this->client->prepare("
            SELECT t.*
            FROM task t
            WHERE t.case_uuid = :caseUuid
        ");

        $stmt->execute([ 'caseUuid' => $caseUuid ]);

        while ($row = $stmt->fetchObject()) {
            $task = new Task();
            $task->uuid = $row->uuid;
            $task->taskType = $row->task_type;
            $task->source = $row->source;
            $task->label = $row->label;
            $task->taskContext = $row->task_context;
            $task->category = $row->category;
            $task->communication = $row->communication;
            $task->dateOfLastExposure =
                !empty($row->date_of_last_exposure) ? new DateTimeImmutable($row->date_of_last_exposure) : null;

            $case->tasks[] = $task;
        }

        return $case;
    }

    /**
     * Submit case tasks.
     *
     * @param string $caseUuid
     * @param string $body
     *
     * @return void
     */
    public function submitCase(string $caseUuid, string $body): void
    {

    }
}

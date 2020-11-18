<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeImmutable;
use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetails;
use DBCO\HealthAuthorityAPI\Application\Models\Answer;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetails;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsFull;
use DBCO\HealthAuthorityAPI\Application\Models\SimpleValue;
use DBCO\HealthAuthorityAPI\Application\Models\Task;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;
use PDO;
use PDOException;
use RuntimeException;

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
            SELECT c.uuid, c.date_of_symptom_onset, c.window_expires_at
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
        $case->windowExpiresAt = new DateTimeImmutable($row->window_expires_at);
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
     * Store case answers.
     *
     * @param CovidCase $case
     *
     * @return void
     */
    public function storeCaseAnswers(CovidCase $case): void
    {
        foreach ($case->tasks as $task) {
            $this->storeTask($case->uuid, $task);
        }
    }

    /**
     * Store case task.
     *
     * @param string $caseUuid
     *
     * @param Task $task
     */
    private function storeTask(string $caseUuid, Task $task)
    {
        if ($task->source !== 'portal') {
            throw new RuntimeException('User created tasks are not yet supported!');
        }

        if (!$task->questionnaireResult) {
            return;
        }

        $stmt = $this->client->prepare("
            UPDATE task
            SET questionnaire_uuid = :questionnaireUuid
            WHERE uuid = :taskUuid
            AND case_uuid = :caseUuid 
        ");

        $stmt->execute([
            'caseUuid' => $caseUuid,
            'questionnaireUuid' => $task->questionnaireResult->questionnaireUuid,
            'taskUuid' => $task->uuid
        ]);

        if ($stmt->rowCount() === 0) {
            return; // task doesn't exist or not part of this case
        }

        foreach ($task->questionnaireResult->answers as $answer) {
            $this->storeAnswer($task, $answer);
        }
    }

    /**
     * Store answer.
     *
     * @param Task   $task
     * @param Answer $answer
     */
    private function storeAnswer(Task $task, Answer $answer)
    {
        $stmt = $this->client->prepare("
            INSERT INTO answer (uuid, task_uuid, question_uuid)
            VALUES (:answerUuid, :taskUuid, :questionUuid)
        ");

        // first register answer
        try {
            $stmt->execute([
                'answerUuid' => $answer->uuid,
                'taskUuid' => $task->uuid,
                'questionUuid' => $answer->questionUuid
            ]);
        } catch (PDOException $e) {
            // answer might already been registered before
        }

        // store answer value
        if ($answer->value instanceof SimpleValue) {
            $stmt = $this->client->prepare("
                UPDATE answer
                SET 
                    question_uuid = :questionUuid,
                    spv_value = :value
                WHERE uuid = :answerUuid
                AND task_uuid = :taskUuid
            ");

            $stmt->execute([
                'answerUuid' => $answer->uuid,
                'taskUuid' => $task->uuid,
                'questionUuid' => $answer->questionUuid,
                'value' => $answer->value->value
            ]);
        } else if ($answer->value instanceof ContactDetails) {
            $stmt = $this->client->prepare("
                UPDATE answer
                SET 
                    question_uuid = :questionUuid,
                    ctd_firstname = :firstName,
                    ctd_lastname = :lastName,
                    ctd_phonenumber = :phoneNumber,
                    ctd_email = :email
                WHERE uuid = :answerUuid
                AND task_uuid = :taskUuid
            ");

            $stmt->execute([
                'answerUuid' => $answer->uuid,
                'taskUuid' => $task->uuid,
                'questionUuid' => $answer->questionUuid,
                'firstName' => $answer->value->firstName,
                'lastName' => $answer->value->lastName,
                'phoneNumber' => $answer->value->phoneNumber,
                'email' => $answer->value->email
            ]);

            if ($answer->value instanceof ContactDetailsFull) {
                // not supported yet
            }
        } else if ($answer->value instanceof ClassificationDetails) {
            $stmt = $this->client->prepare("
                UPDATE answer
                SET 
                    question_uuid = :questionUuid,
                    cfd_cat_1_risk = :cat1Risk,
                    cfd_cat_2a_risk = :cat2ARisk,
                    cfd_cat_2b_risk = :cat2BRisk,
                    cfd_cat_3_risk = :cat3Risk
                WHERE uuid = :answerUuid
                AND task_uuid = :taskUuid
            ");

            $stmt->execute([
                'answerUuid' => $answer->uuid,
                'taskUuid' => $task->uuid,
                'questionUuid' => $answer->questionUuid,
                'cat1Risk' => $answer->value->category1Risk ? 1 : 0,
                'cat2ARisk' => $answer->value->category2ARisk ? 1 : 0,
                'cat2BRisk' => $answer->value->category2BRisk ? 1 : 0,
                'cat3Risk' => $answer->value->category3Risk ? 1 : 0
            ]);
        }
    }
}

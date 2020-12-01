<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTime;
use DateTimeImmutable;
use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetails;
use DBCO\HealthAuthorityAPI\Application\Models\Answer;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetails;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsFull;
use DBCO\HealthAuthorityAPI\Application\Models\SimpleDateValue;
use DBCO\HealthAuthorityAPI\Application\Models\SimpleStringValue;
use DBCO\HealthAuthorityAPI\Application\Models\Task;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;
use PDO;
use RuntimeException;
use stdClass;

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

        $this->markCaseAsSubmitted($case->uuid);
    }

    /**
     * Bump updated field for case.
     *
     * @param string $caseUuid
     */
    private function markCaseAsSubmitted(string $caseUuid)
    {
        $stmt = $this->client->prepare("
            UPDATE covidcase
            SET 
                index_submitted_at = NOW(),
                updated_at = NOW()
            WHERE uuid = :caseUuid
        ");

        $stmt->execute([
            'caseUuid' => $caseUuid
        ]);
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
        // we only store tasks with results
        if (!$task->questionnaireResult) {
            return;
        }

        $taskInfo = $this->getTaskInfo($task);

        $this->validateTaskUpdate($caseUuid, $task, $taskInfo);

        if ($task->source === 'app' && !$taskInfo) {
            // user created task (source "app") needs to be created
            $this->createUserTask($caseUuid, $task);
        } else if ($task->source === 'app') {
            // update some properties that are only updatable
            // when the task originates from the app
            $this->updateUserTask($task);
        } else {
            // update portal task
            $this->updatePortalTask($task);
        }

        // insert / update answers
        foreach ($task->questionnaireResult->answers as $answer) {
            $this->storeAnswer($task, $answer);
        }
    }

    /**
     * Returns basic task info (caseUuid and source).
     *
     * @param Task $task
     *
     * @return stdClass|false Object with caseUuid and source properties or false if non-existent.
     */
    private function getTaskInfo(Task $task)
    {
        $stmt = $this->client->prepare("
            SELECT case_uuid, source
            FROM task
            WHERE uuid = :taskUuid
        ");

        $stmt->execute([
            'taskUuid' => $task->uuid,
        ]);

        $row = $stmt->fetchObject();
        if (!$row) {
            return false;
        }

        return (object)[
            'caseUuid' => $row->case_uuid,
            'source' => $row->source
        ];
    }

    /**
     * Validate task update.
     *
     * @param string         $caseUuid
     * @param Task           $task
     * @param stdClass|false $taskInfo
     */
    private function validateTaskUpdate(string $caseUuid, Task $task, $taskInfo): void
    {
        // TODO:
        //   Do we throw an error and throw away all other data as well, or do we
        //   silently ignore the invalid data? Especially for the check for a non
        //   existing task that originates from the portal. What happens if the
        //   task has been deleted in the portal?

        if ($taskInfo && $taskInfo->caseUuid !== $caseUuid) {
            throw new RuntimeException("Update for task belonging to different case ({$taskInfo->caseUuid} != {$caseUuid}) " . print_r($taskInfo, true));
        } else if ($taskInfo && $taskInfo->source !== $task->source) {
            throw new RuntimeException("Task update with different source than original");
        } else if ($taskInfo && $taskInfo->source === 'app' && $task->taskType !== 'contact') {
            throw new RuntimeException("Task with source app uses unsupported task type");
        } else if (!$taskInfo && $task->source === 'portal') {
            throw new RuntimeException("Task with source portal does not exist");
        }
    }

    /**
     * Create user task (source "app").
     *
     * @param string $caseUuid
     * @param Task   $task
     */
    private function createUserTask(string $caseUuid, Task $task): void
    {
        $stmt = $this->client->prepare("
            INSERT INTO task (
                uuid, case_uuid, task_type, source, label, task_context, 
                category, communication, questionnaire_uuid, informed_by_index, created_at, updated_at
            )
            VALUES (
                :taskUuid, :caseUuid, :taskType, :source, :label, :taskContext, 
                :category, :communication, :questionnaireUuid, 0, NOW(), NOW()
            )
        ");

        $stmt->execute([
            'taskUuid' => $task->uuid,
            'caseUuid' => $caseUuid,
            'taskType' => $task->taskType,
            'source' => $task->source,
            'label' => $task->label,
            'taskContext' => $task->taskContext,
            'category' => $task->category,
            'communication' => $task->communication,
            'questionnaireUuid' => $task->questionnaireResult->questionnaireUuid
        ]);
    }

    /**
     * Update user created task.
     *
     * @param Task $task
     */
    private function updateUserTask(Task $task): void
    {
        $stmt = $this->client->prepare("
            UPDATE task
            SET
                label = :label,
                task_context = :taskContext,
                category = :category,
                communication = :communication,
                questionnaire_uuid = :questionnaireUuid,
                updated_at = NOW()
            WHERE uuid = :taskUuid
        ");

        $stmt->execute([
            'label' => $task->label,
            'taskContext' => $task->taskContext,
            'category' => $task->category,
            'communication' => $task->communication,
            'questionnaireUuid' => $task->questionnaireResult->questionnaireUuid,
            'taskUuid' => $task->uuid
        ]);
    }

    /**
     * Update portal task.
     *
     * @param Task $task
     */
    private function updatePortalTask(Task $task): void
    {
        $stmt = $this->client->prepare("
            UPDATE task
            SET 
                questionnaire_uuid = :questionnaireUuid, 
                updated_at = NOW()
            WHERE uuid = :taskUuid
        ");

        $stmt->execute([
            'questionnaireUuid' => $task->questionnaireResult->questionnaireUuid,
            'taskUuid' => $task->uuid
        ]);
    }

    /**
     * Store answer.
     *
     * @param Task   $task
     * @param Answer $answer
     */
    private function storeAnswer(Task $task, Answer $answer)
    {
        $answerInfo = $this->getAnswerInfo($task, $answer);

        if ($answerInfo) {
            // ignore given uuid and use the one we've determined ourselves
            $answer->uuid = $answerInfo->uuid;
        } else {
            // first create answer
            $this->createAnswer($task, $answer);
        }

        // update answer
        if ($answer->value instanceof SimpleStringValue) {
            $this->updateSimpleStringValueAnswer($answer, $answer->value);
        } else if ($answer->value instanceof SimpleDateValue) {
            $this->updateSimpleDateValueAnswer($answer, $answer->value);
        } else if ($answer->value instanceof ContactDetails) {
            $this->updateContactDetailsAnswer($answer, $answer->value);
        } else if ($answer->value instanceof ClassificationDetails) {
            $this->updateClassificationDetailsAnswer($answer, $answer->value);
        }
    }

    /**
     * Returns basic answer info (uuid).
     *
     * @param Task   $task
     * @param Answer $answer
     *
     * @return stdClass|false Object with uuid or false if non-existent.
     */
    private function getAnswerInfo(Task $task, Answer $answer)
    {
        // NOTE:
        // We don't simply use the uuid from the answer because the client might simply not know the correct uuid
        // anymore (re-pairing to a different device) or can't be trusted for different reasons. We know currently
        // there is always at most one answer for each task/question combination.
        //
        // In a future update there can be multiple answers for a task (one entered by the health authority user,
        // one entered by the index and one that reflects the current value). We will be able to determine the correct
        // one here based on a type or source field.
        
        $stmt = $this->client->prepare("
            SELECT uuid
            FROM answer
            WHERE task_uuid = :taskUuid
            AND question_uuid = :questionUuid
        ");

        $stmt->execute([
            'taskUuid' => $task->uuid,
            'questionUuid' => $answer->questionUuid
        ]);

        $row = $stmt->fetchObject();
        if (!$row) {
            return false;
        }

        return (object)[
            'uuid' => $row->uuid
        ];
    }

    /**
     * Create answer.
     *
     * @param Task   $task
     * @param Answer $answer
     */
    private function createAnswer(Task $task, Answer $answer)
    {
        $stmt = $this->client->prepare("
            INSERT INTO answer (uuid, task_uuid, question_uuid, created_at, updated_at)
            VALUES (:answerUuid, :taskUuid, :questionUuid, :updatedAt, :updatedAt)
        ");

        $stmt->execute([
            'answerUuid' => $answer->uuid,
            'taskUuid' => $task->uuid,
            'questionUuid' => $answer->questionUuid,
            'updatedAt' => $answer->lastModified->format(DateTime::ATOM)
        ]);
    }

    /**
     * Update simple string value answer.
     *
     * @param Answer            $answer
     * @param SimpleStringValue $value
     */
    private function updateSimpleStringValueAnswer(Answer $answer, SimpleStringValue $value): void
    {
        $stmt = $this->client->prepare("
            UPDATE answer
            SET 
                question_uuid = :questionUuid,
                spv_value = :value,
                updated_at = GREATEST(updated_at, :updatedAt)
            WHERE uuid = :answerUuid
        ");

        $stmt->execute([
            'answerUuid' => $answer->uuid,
            'questionUuid' => $answer->questionUuid,
            'value' => $value->value,
            'updatedAt' => $answer->lastModified->format(DateTime::ATOM)
        ]);
    }


    /**
     * Update simple date value answer.
     *
     * @param Answer          $answer
     * @param SimpleDateValue $value
     */
    private function updateSimpleDateValueAnswer(Answer $answer, SimpleDateValue $value): void
    {
        $stmt = $this->client->prepare("
            UPDATE answer
            SET 
                question_uuid = :questionUuid,
                spv_value = :value,
                updated_at = GREATEST(updated_at, :updatedAt)
            WHERE uuid = :answerUuid
        ");

        $stmt->execute([
            'answerUuid' => $answer->uuid,
            'questionUuid' => $answer->questionUuid,
            'value' => $value->value !== null ? $value->value->format('Y-m-d') : null,
            'updatedAt' => $answer->lastModified->format(DateTime::ATOM)
        ]);
    }

    /**
     * Update contact details answer
     *
     * @param Answer         $answer
     * @param ContactDetails $value
     */
    private function updateContactDetailsAnswer(Answer $answer, ContactDetails $value): void
    {
        $stmt = $this->client->prepare("
            UPDATE answer
            SET 
                question_uuid = :questionUuid,
                ctd_firstname = :firstName,
                ctd_lastname = :lastName,
                ctd_phonenumber = :phoneNumber,
                ctd_email = :email,
                updated_at = GREATEST(updated_at, :updatedAt)
            WHERE uuid = :answerUuid
        ");

        $stmt->execute([
            'answerUuid' => $answer->uuid,
            'questionUuid' => $answer->questionUuid,
            'firstName' => $value->firstName,
            'lastName' => $value->lastName,
            'phoneNumber' => $value->phoneNumber,
            'email' => $value->email,
            'updatedAt' => $answer->lastModified->format(DateTime::ATOM)
        ]);

        if ($value instanceof ContactDetailsFull) {
            // not supported yet
        }
    }

    /**
     * Update classification details answer.
     *
     * @param Answer                $answer
     * @param ClassificationDetails $value
     */
    private function updateClassificationDetailsAnswer(Answer $answer, ClassificationDetails $value): void
    {
        $stmt = $this->client->prepare("
            UPDATE answer
            SET 
                question_uuid = :questionUuid,
                cfd_cat_1_risk = :cat1Risk,
                cfd_cat_2a_risk = :cat2ARisk,
                cfd_cat_2b_risk = :cat2BRisk,
                cfd_cat_3_risk = :cat3Risk,
                updated_at = GREATEST(updated_at, :updatedAt)
            WHERE uuid = :answerUuid
        ");

        $stmt->execute([
            'answerUuid' => $answer->uuid,
            'questionUuid' => $answer->questionUuid,
            'cat1Risk' => $value->category1Risk ? 1 : 0,
            'cat2ARisk' => $value->category2ARisk ? 1 : 0,
            'cat2BRisk' => $value->category2BRisk ? 1 : 0,
            'cat3Risk' => $value->category3Risk ? 1 : 0,
            'updatedAt' => $answer->lastModified->format(DateTime::ATOM)
        ]);
    }

    /**
     * Mark case as paired.
     *
     * @param string $caseUuid
     */
    public function markCaseAsPaired(string $caseUuid): void
    {
        $stmt = $this->client->prepare("
            UPDATE covidcase
            SET 
                updated_at = NOW(), 
                status = :status
            WHERE uuid = :caseUuid
        ");

        $stmt->execute([
            'caseUuid' => $caseUuid,
            'status' => 'paired'
        ]);
    }
}

<?php

namespace MinVWS\DBCO\Metrics\Repositories;

use DateTimeZone;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Repositories\DbStorageRepository as BaseDbStorageRepository;
use PDO;

use function array_key_exists;
use function json_encode;

/**
 * Store events and exports in database.
 *
 * @package MinVWS\Metrics\Repositories
 */
class EventDbStorageRepository extends BaseDbStorageRepository
{
    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    /**
     * Constructor.
     */
    public function __construct(PDO $client, EncryptionHelper $encryptionHelper)
    {
        parent::__construct($client);
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * Retrieve data from task need for comparing updates
     *
     * @param string $taskUuid
     * @return array
     */
    public function getTaskData(string $taskUuid): array
    {
        $task = $this->fetchTask($taskUuid);
        if (!$task) {
            return [];
        }

        $taskData = $task;
        $taskData['general'] = !empty($task['general']) ? json_decode($this->encryptionHelper->unsealOptionalStoreValue($task['general']), true) : null;

        $contactDetailsAnswer = $this->fetchContactDetailsAnswer($taskUuid);
        if ($contactDetailsAnswer) {
            $contactDetailsAnswer = $this->decryptAnswers($contactDetailsAnswer);
            $taskData = array_merge($taskData, $contactDetailsAnswer);
        }
        return $taskData;
    }

    public function createEvent(Event $event): void
    {
        $executeParams = [
            'uuid' => $event->uuid,
            'type' => $event->type,
            'data' => json_encode($event->data),
            'exportData' => json_encode($event->exportData),
            'createdAt' => $event->createdAt->setTimezone(new DateTimeZone('UTC'))->format("Y-m-d H:i:s"),
            'organisationUuid' => null,
        ];

        if (array_key_exists('caseUuid', $event->data)) {
            $stmt = $this->client->prepare("
                SELECT `organisation_uuid`
                FROM `covidcase`
                WHERE `uuid` = :caseUuid
            ");
            $stmt->execute(['caseUuid' => $event->data['caseUuid']]);
            $row = $stmt->fetchObject();
            if ($row) {
                $executeParams['organisationUuid'] = $row->organisation_uuid;
            }
        }

        $stmt = $this->client->prepare("
            INSERT INTO event (uuid, type, data, export_data, created_at, organisation_uuid)
            VALUES (:uuid, :type, :data, :exportData, :createdAt, :organisationUuid)
        ");
        $stmt->execute($executeParams);
    }

    /**
     * Fetch task data need for updated field comparing
     *
     * @param string $taskUuid
     * @return object|mixed false when not found
     */
    private function fetchTask(string $taskUuid)
    {
        $stmt = $this->client->prepare("
            SELECT
               t.uuid,
               t.case_uuid,
               t.date_of_last_exposure,
               t.informed_by_index_at,
               t.informed_by_staff_at,
               t.general
            FROM task t 
            WHERE t.uuid = :taskUuid
        ");

        $stmt->execute(['taskUuid' => $taskUuid]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch answer data for contactdetails needed for updated field comparing
     *
     * @param $taskUuid
     * @return array|mixed false when not found
     */
    private function fetchContactDetailsAnswer($taskUuid)
    {
        $stmt = $this->client->prepare("
                    SELECT
                       a.ctd_lastname,
                       a.ctd_firstname,
                       a.ctd_phonenumber,
                       a.ctd_email
                    FROM answer a
                    JOIN question q ON (q.uuid = a.question_uuid)
                    WHERE a.task_uuid = :taskUuid AND q.question_type = 'contactdetails'
                    LIMIT 1
                ");
        $stmt->execute(['taskUuid' => $taskUuid]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Decrypt answer field values
     *
     * @param array $answers
     * @return array
     */
    private function decryptAnswers(array $answers)
    {
        return array_combine(
            array_keys($answers),
            array_map(fn ($a) => $this->encryptionHelper->unsealOptionalStoreValue($a), array_values($answers))
        );
    }
}

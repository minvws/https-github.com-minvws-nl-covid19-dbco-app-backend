<?php

namespace DBCO\Shared\Application\Metrics\Transformers;

use DateTime;
use Exception;
use MinVWS\DBCO\Metrics\Services\TaskProgressService;
use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Transformers\EventTransformer as EventTransformerInterface;
use PDO;

/**
 * Transform / enrich event data.
 */
class EventTransformer implements EventTransformerInterface
{
    /**
     * @var PDO
     */
    private PDO $client;

    /**
     * @var TaskProgressService
     */
    private TaskProgressService $taskProgressService;

    /**
     * Constructor.
     *
     * @param PDO $client
     */
    public function __construct(PDO $client, TaskProgressService $taskProgressService)
    {
        $this->client = $client;
        $this->taskProgressService = $taskProgressService;
    }

    /**
     * Fetch case data.
     *
     * @param Event $event
     *
     * @return object|false
     */
    private function fetchCaseData(Event $event)
    {
        $stmt = $this->client->prepare("
            SELECT
               c.created_at,
               c.date_of_symptom_onset,
               o.external_id AS organisation_external_id
            FROM covidcase c 
            JOIN organisation o ON (o.uuid = c.organisation_uuid)
            WHERE c.uuid = :caseUuid
        ");

        $stmt->execute(['caseUuid' => $event->data['caseUuid']]);

        return $stmt->fetchObject();
    }

    /**
     * Export data for case.
     *
     * @param Event $event
     *
     * @return array
     *
     * @throws Exception
     */
    private function exportDataForCase(Event $event): array
    {
        $caseData = $this->fetchCaseData($event);
        if (!$caseData) {
            return [];
        }

        $exportData = [
            'id' => $event->uuid,
            'event' => $event->type,
            'date' => $event->createdAt->format('Y-m-d'),
            'ts_delta' => $event->createdAt->getTimestamp() - (new DateTime($caseData->created_at))->getTimestamp(),
            'actor' => $event->data['actor'],
            'pseudo_id' => hash('sha256', $event->data['caseUuid']),
            'vrregioncode' => $caseData->organisation_external_id
        ];

        if (in_array($event->type, ['opened', 'identified', 'inventoried', 'edited'])) {
            $exportData['date_of_symptom_onset'] = $caseData->date_of_symptom_onset;
        }

        return $exportData;
    }

    /**
     * Fetch task data.
     *
     * @param Event $event
     *
     * @return object|false
     */
    private function fetchTaskData(Event $event)
    {
        $stmt = $this->client->prepare("
            SELECT
               t.category,
               t.export_id
            FROM task t 
            WHERE t.uuid = :taskUuid
        ");

        $stmt->execute(['taskUuid' => $event->data['taskUuid']]);

        return $stmt->fetchObject();
    }

    /**
     * Export data for task specific events.
     *
     * @param Event  $event
     *
     * @return array
     *
     * @throws Exception
     */
    private function exportDataForTask(Event $event): array
    {
        if (!in_array($event->type, ['identified', 'inventoried', 'edited', 'exported', 'informed'])) {
            return [];
        }

        $taskData = $this->fetchTaskData($event);
        if (!$taskData) {
            return []; // TODO: throw error?
        }

        $exportData = [
            'contact_pseudo_id' => hash('sha256', $event->data['taskUuid']),
            'category' => $taskData->category
        ];

        if (in_array($event->type, ['inventoried', 'edited'])) {
            $exportData['fields'] = implode(",", $event->data['taskFields']);
            $exportData['progress'] = $this->taskProgressService->getProgress($event->data['taskUuid']);
        }

        return $exportData;
    }

    /**
     * @inheritDoc
     */
    public function exportDataForEvent(Event $event): array
    {
        $caseExportData = $this->exportDataForCase($event);
        if (count($caseExportData) === 0) {
            return [];
        }

        $taskExportData = $this->exportDataForTask($event);

        return array_merge($caseExportData, $taskExportData);
    }
}

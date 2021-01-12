<?php
namespace DBCO\Shared\Application\Metrics\Transformers;

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
     * Constructor.
     *
     * @param PDO $client
     */
    public function __construct(PDO $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function exportDataForEvent(Event $event): array
    {
        $stmt = $this->client->prepare("
            SELECT
               c.created_at,
               c.date_of_symptom_onset,
               o.external_id AS organisation_external_id
            FROM covidcase c 
            JOIN organisation ON (o.uuid = c.origanisation_uuid)
            WHERE c.uuid = :caseUuid
        ");

        $stmt->execute(['caseUuid' => $event->data['caseUuid']]);

        $row = $this->client->fetchObject();
        if (!$row) {
            return []; // TODO: throw error?
        }

        $exportData = [
            'id' => $event->uuid,
            'event' => $event->type,
            'date' => $event->createdAt->format('Y-m-d'),
            'ts_delta' => $event->createdAt->diff(new DateTime($row->created_at)),
            'actor' => $event->data['actor'],
            'pseudo_id' => hash('sha256', $event->data['caseUuid']),
            'vrregioncode' => $row->organisation_external_id
        ];

        if ($event->type === 'opened') {
            $exportData['date_of_symptom_onset'] = $row->date_of_symptom_onset;
        }

        if (in_array($event->type, ['identified', 'inventoried', 'edited', 'exported', 'informed'])) {
            $stmt = $this->client->prepare("
                SELECT
                   t.category,
                   t.export_id
                FROM task t 
                WHERE t.uuid = :taskUuid
            ");

            $stmt->execute(['taskUuid' => $event->data['taskUuid']]);

            $taskRow = $this->client->fetchObject();
            if (!$taskRow) {
                return []; // TODO: throw error?
            }

            $exportData['contact_pseudo_id'] = hash('sha256', $event->data['taskUuid']);
            $exportData['category'] = $taskRow->category;

            if (in_array($event->type, ['inventoried', 'edited'])) {
                $exportData['fields'] = $event->data['taskFields'];
            } else if ($event->type === 'exported') {
                $exportData['hpzone_id'] = $taskRow->export_id;
            }
        }

        return $exportData;
    }
}
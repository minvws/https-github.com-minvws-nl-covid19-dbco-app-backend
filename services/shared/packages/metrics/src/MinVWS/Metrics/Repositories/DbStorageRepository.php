<?php
namespace MinVWS\Metrics\Repositories;

use Closure;
use DateTime;
use DateTimeImmutable;
use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;
use PDO;

/**
 * Store events and exports in database.
 *
 * @package MinVWS\Metrics\Repositories
 */
class DbStorageRepository implements StorageRepository
{
    /**
     * @var PDO
     */
    private PDO $client;

    /**
     * Constructor.
     */
    public function __construct(PDO $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function createEvent(Event $event): void
    {
        $stmt = $this->client->prepare("
            INSERT INTO event (uuid, data, export_data, created_at)
            VALUES (:uuid, :status, :data, :exportData, :createdAt)
        ");

        $stmt->execute([
            'uuid' => $event->uuid,
            'data' => json_encode($event->data),
            'exportData' => json_encode($event->exportData),
            'createdAt' => $event->createdAt->format(DateTime::ATOM)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createExport(Export $export): void
    {
        $stmt = $this->client->prepare("
            INSERT INTO export (uuid, status, created_at)
            VALUES (:uuid, :status, :createdAt)
        ");

        $stmt->execute([
            'uuid' => $export->uuid,
            'status' => $export->status,
            'createdAt' => $export->createdAt->format(DateTime::ATOM)
        ]);

        $stmt = $this->client->prepare("
            UPDATE event 
            SET export_uuid = :exportUuid
            WHERE export_uuid IS NULL
        ");

        $stmt->execute([
            'exportUuid' => $export->uuid,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function iterateEventsForExport(string $exportUuid, Closure $callback): void
    {
        $stmt = $this->client->prepare("
            SELECT uuid, type, data, export_data, created_at
            FROM event 
            WHERE export_uuid = :exportUuid
        ");

        $stmt->execute([
            'exportUuid' => $exportUuid
        ]);

        while ($row = $stmt->fetchObject()) {
            $event = new Event(
                $row->uuid,
                $row->type,
                json_decode($row->data, false),
                json_decode($row->export_data, false),
                new DateTimeImmutable($row->created_at)
            );

            $callback($event);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateExport(Export $export, array $fields): void
    {
        $params = ['uuid' => $export->uuid];
        $setStatements = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'status':
                case 'filename':
                    $setStatements[] = "$field = :$field";
                    $params[$field] = $export->$field;
                    break;
                case 'exportedAt':
                case 'uploadedAt':
                    $setStatements[] = str_replace('At', '_at', $field) . " = :$field";
                    $params[$field] = isset($export->$field) ? $export->$field->format(DateTime::ATOM) : null;
                    break;
            }
        }

        if (count($setStatements) === 0) {
            return;
        }

        $stmt = $this->client->prepare("
            UPDATE export
            SET " . implode(', ', $setStatements) . "
            WHERE uuid = :uuid
        ");

        $stmt->execute($params);
    }
}
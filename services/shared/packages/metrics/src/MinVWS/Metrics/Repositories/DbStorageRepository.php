<?php

namespace MinVWS\Metrics\Repositories;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;
use PDO;
use stdClass;

use function json_encode;

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
    protected PDO $client;

    /**
     * Constructor.
     */
    public function __construct(PDO $client)
    {
        $this->client = $client;
    }

    /**
     * Add status to export where clause.
     *
     * @param string     $query
     * @param array      $params
     * @param array|null $status
     */
    protected function addStatusToExportWhereClause(string &$query, array &$params, ?array $status)
    {
        if ($status !== null && count($status) >= 1) {
            $in = '';
            foreach ($status as $i => $s) {
                $in .= (strlen($in) > 0 ? ', ' : '') . ':status' . $i;
                $params['status' . $i] = $s;
            }

            $query .= "WHERE status IN ($in)\n";
        } elseif ($status !== null) {
            $query .= "WHERE 1 = 0\n";
        }
    }

    /**
     * Convert export database row to entity.
     *
     * @param stdClass $row
     * @return Export
     *
     * @throws Exception
     */
    protected function exportRowToEntity(stdClass $row): Export
    {
        return new Export(
            $row->uuid,
            $row->status,
            new DateTimeImmutable($row->created_at),
            $row->filename,
            $row->exported_at !== null ? new DateTimeImmutable($row->exported_at, new DateTimeZone('UTC')) : null,
            $row->uploaded_at !== null ? new DateTimeImmutable($row->uploaded_at, new DateTimeZone('UTC')) : null,
            $row->item_count ?? null
        );
    }

    /**
     * @inheritdoc
     */
    public function countExports(?array $status): int
    {
        $params = [];

        $query = "
            SELECT COUNT(*) 
            FROM export 
        ";

        $this->addStatusToExportWhereClause($query, $params, $status);

        $stmt = $this->client->prepare($query);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    /**
     * @inheritdoc
     */
    public function listExports(int $limit, int $offset, ?array $status): array
    {
        $params = [];

        $query = "
            SELECT 
               uuid, status, created_at, filename, exported_at, uploaded_at, 
               (SELECT COUNT(*) FROM event WHERE export_uuid = export.uuid) AS item_count
            FROM export 
        ";

        $this->addStatusToExportWhereClause($query, $params, $status);

        $query .= "ORDER BY created_at DESC\n";
        $query .= sprintf("LIMIT %d, %d", $offset, $limit);

        $stmt = $this->client->prepare($query);
        $stmt->execute($params);

        $exports = [];
        while ($row = $stmt->fetchObject()) {
            $exports[] = $this->exportRowToEntity($row);
        }

        return $exports;
    }

    /**
     * Retrieve export.
     *
     * @param string $exportUuid
     *
     * @return Export|null
     */
    public function getExport(string $exportUuid): ?Export
    {
        $stmt = $this->client->prepare("
            SELECT
               uuid, status, created_at, filename, exported_at, uploaded_at, 
               (SELECT COUNT(*) FROM event WHERE export_uuid = export.uuid) AS item_count
            FROM export
            WHERE uuid = :uuid
        ");

        $stmt->execute(['uuid' => $exportUuid]);

        $row = $stmt->fetchObject();
        if ($row !== null) {
            return $this->exportRowToEntity($row);
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function createEvent(Event $event): void
    {
        $stmt = $this->client->prepare("
            INSERT INTO event (uuid, type, data, export_data, created_at)
            VALUES (:uuid, :type, :data, :exportData, :createdAt)
        ");

        $stmt->execute([
            'uuid' => $event->uuid,
            'type' => $event->type,
            'data' => json_encode($event->data),
            'exportData' => json_encode($event->exportData),
            'createdAt' => $event->createdAt->setTimezone(new DateTimeZone('UTC'))->format("Y-m-d H:i:s")
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createExport(Export $export, ?int $limit): void
    {
        $stmt = $this->client->prepare("
            INSERT INTO export (uuid, status, created_at)
            VALUES (:uuid, :status, :createdAt)
        ");

        $stmt->execute([
            'uuid' => $export->uuid,
            'status' => $export->status,
            'createdAt' => $export->createdAt->setTimezone(new DateTimeZone('UTC'))->format("Y-m-d H:i:s")
        ]);

        $query = "
            UPDATE event 
            SET export_uuid = :exportUuid
            WHERE export_uuid IS NULL
        ";

        $params = [
            'exportUuid' => $export->uuid,
        ];

        if ($limit !== null) {
            $query .= ' ORDER BY created_at ASC LIMIT :limit';
            $params['limit'] = $limit;
        }

        $stmt = $this->client->prepare($query);
        $stmt->execute($params);
    }

    /**
     * @inheritdoc
     */
    public function iterateForExport(string $exportUuid, Closure $callback): void
    {
        $stmt = $this->client->prepare("
            SELECT uuid, type, data, export_data, created_at
            FROM event 
            WHERE export_uuid = :exportUuid
            ORDER BY created_at
        ");

        $stmt->execute([
            'exportUuid' => $exportUuid
        ]);

        while ($row = $stmt->fetchObject()) {
            $event = new Event(
                $row->uuid,
                $row->type,
                json_decode($row->data, true),
                json_decode($row->export_data, true),
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
                    $params[$field] = isset($export->$field) ? $export->$field->setTimezone(new DateTimeZone('UTC'))->format("Y-m-d H:i:s") : null;
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

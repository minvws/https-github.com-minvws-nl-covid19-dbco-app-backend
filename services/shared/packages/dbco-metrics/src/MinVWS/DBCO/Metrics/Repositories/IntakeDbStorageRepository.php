<?php

namespace MinVWS\DBCO\Metrics\Repositories;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use MinVWS\DBCO\Encryption\Security\EncryptionException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\Metrics\Models\Export;
use MinVWS\DBCO\Metrics\Models\Intake;
use MinVWS\Metrics\Repositories\DbStorageRepository as BaseDbStorageRepository;
use PDO;
use Psr\Log\LoggerInterface;
use SodiumException;

/**
 * Store events and exports in database.
 *
 * @package MinVWS\Metrics\Repositories
 */
class IntakeDbStorageRepository extends BaseDbStorageRepository
{
    protected EncryptionHelper $encryptionHelper;

    private LoggerInterface $logger;

    /**
     * Constructor.
     */
    public function __construct(PDO $client, EncryptionHelper $encryptionHelper, LoggerInterface $logger)
    {
        parent::__construct($client);
        $this->encryptionHelper = $encryptionHelper;
        $this->logger = $logger;
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
               (SELECT COUNT(*) FROM pseudonymous_intake WHERE export_uuid = export.uuid) AS item_count
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
     * @param Export      $export
     * @param int|null    $limit
     * @param string|null $type Type of intake: bco, selftest
     */
    public function createIntakeExport(Export $export, ?int $limit, ?string $type = null): void
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
            UPDATE pseudonymous_intake
            SET export_uuid = :exportUuid
            WHERE export_uuid IS NULL
        ";

        $params = [
            'exportUuid' => $export->uuid,
        ];

        if (!empty($type)) {
            $query .= ' AND type = :intakeType';
            $params['intakeType'] = $type;
        }

        if ($limit !== null) {
            $query .= ' ORDER BY created_at ASC LIMIT :limit';
            $params['limit'] = $limit;
        }

        $stmt = $this->client->prepare($query);
        $stmt->execute($params);
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
               (SELECT COUNT(*) FROM pseudonymous_intake WHERE export_uuid = export.uuid) AS item_count
            FROM export
            WHERE uuid = :uuid
        ");

        $stmt->execute(['uuid' => $exportUuid]);

        $row = $stmt->fetchObject();

        if ($row !== false) {
            return $this->exportRowToEntity($row);
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function iterateForExport(string $exportUuid, Closure $callback): void
    {
        $stmt = $this->client->prepare("
            SELECT uuid, type, data, created_at
            FROM pseudonymous_intake 
            WHERE export_uuid = :exportUuid
            ORDER BY created_at
        ");

        $stmt->execute([
            'exportUuid' => $exportUuid
        ]);

        while ($row = $stmt->fetchObject()) {
            $data = $this->decryptPseudonymizedIntakeData($row);
            $intake = new Intake(
                $row->uuid,
                $row->type,
                json_decode($data, true),
                new DateTimeImmutable($row->created_at)
            );

            $callback($intake);
        }
    }

    private function decryptPseudonymizedIntakeData($row)
    {
        try {
            return $this->encryptionHelper->unsealOptionalStoreValue($row->data);
            ;
        } catch (SodiumException $e) {
            $this->logger->error('SodiumException decrypting pseudonymized intake: ' . $row->uuid);
            return null;
        } catch (EncryptionException $e) {
            $this->logger->error('EncryptionException decrypting pseudonymized intake: ' . $row->uuid);
            // not a valid key anymore?
            return null;
        }
    }
}

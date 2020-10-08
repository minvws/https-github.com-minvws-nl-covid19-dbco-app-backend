<?php
namespace DBCO\Application\Repositories;

use DateTime;
use DateTimeInterface;
use DBCO\Application\Models\DbcoCase;
use DBCO\Application\Models\Pairing;
use PDO;
use RuntimeException;

/**
 * Store/retrieve pairings in/from the database.
 *
 * @package DBCO\Application\Repositories
 */
class DbPairingRepository implements PairingRepository
{
    private const MAPPING = [
        'code' => 'code',
        'codeExpiresAt' => 'code_expires_at',
        'isPaired' => 'is_paired',
        'signingKey' => 'signing_key'
    ];

    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * Constructor.
     *
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getPairingByCode(string $code): ?Pairing
    {
        $values = [
            'code' => $code,
        ];

        $sql = "
            SELECT p.*, c.expires_at AS case_expires_at
            FROM pairing p
            JOIN \"case\" c ON (c.id = p.case_id)
            WHERE p.code = :code
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);

        $row = $stmt->fetchObject();
        if ($row === false) {
            return null;
        }

        $case =
            new DbcoCase(
                $row->case_id,
                new DateTime($row->case_expires_at)
            );

        $pairing =
            new Pairing(
                $row->id,
                $case,
                $row->code,
                $row->code_expires_at != null ? new DateTime($row->code_expires_at) : null,
                $row->is_paired === 1,
                $row->signing_key
            );

        return $pairing;
    }

    /**
     * Map field names to columns.
     *
     * @param array $fields
     *
     * @return array
     */
    private function mapFieldsToColumns(array $fields): array
    {
        return array_map(fn ($f) => self::MAPPING[$f], $fields);
    }

    /**
     * Extract column values from the given model.
     *
     * @param Pairing $pairing
     * @param array   $columns
     *
     * @return array
     */
    private function extractColumnValues(Pairing $pairing, array $columns): array
    {
        $values = [
            'id' => $pairing->id,
            'case_id' => $pairing->case->id,
            'code' => $pairing->code,
            'code_expires_at' => $pairing->codeExpiresAt != null ? $pairing->codeExpiresAt->format(\DateTime::ATOM) : null,
            'is_paired' => $pairing->isPaired ? 1 : 0,
            'signing_key' => $pairing->signingKey
        ];

        $values = array_filter($values, fn ($k) => in_array($k, $columns), ARRAY_FILTER_USE_KEY);

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function createPairing(Pairing $pairing)
    {
        $values = $this->extractColumnValues($pairing, ['case_id', 'code', 'code_expires_at', 'is_paired', 'signing_key']);

        $sql = "
            INSERT INTO pairing (case_id, code, code_expires_at, is_paired, signing_key) 
            VALUES (:case_id, :code, :code_expires_at, :is_paired, :signing_key)
        ";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Error preparing query');
        }

        $stmt->execute($values);

        $pairing->id = (string)$this->connection->lastInsertId('pairing_id_seq');
    }

    /**
     * @inheritDoc
     */
    public function updatePairing(Pairing $pairing, array $fields)
    {
        $columns = $this->mapFieldsToColumns($fields);

        $pairs = [];
        foreach ($columns as $column) {
            $pairs[] = "$column = :$column";
        }

        $sql = "UPDATE pairing SET " . implode(', ', $pairs) . " WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Error preparing query');
        }

        $values = $this->extractColumnValues($pairing, $columns);
        $values['id'] = $pairing->id;

        $stmt->execute($values);
    }

    /**
     * @inheritDoc
     */
    public function deletePairingsWithExpiresAtBefore(DateTimeInterface $expiresAtBefore)
    {
        $sql = "
            DELETE FROM pairing
            WHERE code_expires_at < :code_expires_at
        ";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Error preparing query');
        }

        $values = [
            'code_expires_at' => $expiresAtBefore->format(DateTime::ATOM)
        ];

        $stmt->execute($values);
    }


    /**
     * @inheritDoc
     */
    public function deletePairingWithCodeAndExpiresAtBefore(string $code, DateTimeInterface $expiresAtBefore)
    {
        $sql = "
            DELETE FROM pairing
            WHERE code = :code
            AND code_expires_at < :code_expires_at
        ";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Error preparing query');
        }

        $values = [
            'code' => $code,
            'code_expires_at' => $expiresAtBefore->format(DateTime::ATOM)
        ];

        $stmt->execute($values);
    }
}

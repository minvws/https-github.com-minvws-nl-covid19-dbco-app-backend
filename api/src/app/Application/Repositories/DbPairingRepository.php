<?php
namespace App\Application\Repositories;

use App\Application\Models\Pairing;
use DateTimeInterface;
use PDO;

/**
 * Store/retrieve pairings in/from the database.
 *
 * @package App\Application\Repositories
 */
class DbPairingRepository implements PairingRepository
{
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
     * Delete expired pairing code (if exists).
     *
     * @param string $code
     */
    protected function deleteExpiredPairingCode(string $code)
    {
        $sql = "
            DELETE FROM pairing
            WHERE code = :code
            AND expires_at <= NOW() 
        ";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new \RuntimeException('Error preparing query');
        }

        $values = [
            'code' => $code
        ];

        $stmt->execute($values);
    }

    /**
     * @inheritDoc
     */
    public function createPairing(string $caseId, string $code, DateTimeInterface $codeExpiresAt): Pairing
    {
        $this->deleteExpiredPairingCode($code);

        $values = [
            'case_id' => $caseId,
            'code' => $code,
            'expires_at' => $codeExpiresAt->format(\DateTime::ATOM),
            'is_paired' => 0
        ];

        $sql = "
            INSERT INTO pairing (case_id, code, expires_at, is_paired) 
            VALUES (:case_id, :code, :expires_at, :is_paired)
        ";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new \RuntimeException('Error preparing query');
        }

        $stmt->execute($values);

        $id = (string)$this->connection->lastInsertId('pairing_id_seq');

        return new Pairing($id, $caseId, $code, $codeExpiresAt, false);
    }

    /**
     * @inheritDoc
     */
    public function isActivePairingCode(string $code): bool
    {
        $values = [
            'code' => $code,
        ];

        $sql = "
            SELECT COUNT(1) as total
            FROM pairing
            WHERE code = :code
            AND expires_at > NOW()
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);

        $total = (int)$stmt->fetchColumn(0);

        return $total > 0;
    }
}

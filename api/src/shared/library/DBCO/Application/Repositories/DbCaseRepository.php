<?php
declare(strict_types=1);

namespace DBCO\Application\Repositories;

use DBCO\Application\Models\DbcoCase;
use DateTime;
use PDO;
use RuntimeException;

/**
 * Store / retrieve case information in / from the database.
 */
class DbCaseRepository implements CaseRepository
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
     * @inheritDoc
     */
    public function createCase(DbcoCase $case)
    {
        $values = [
            'id' => $case->id,
            'expires_at' => $case->expiresAt->format(DateTime::ATOM)
        ];

        $sql = "
            INSERT INTO \"case\" (id, expires_at) 
            VALUES (:id, :expires_at)
        ";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Error preparing query');
        }

        $stmt->execute($values);
    }
}

<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Models\DbcoCase;
use DateTimeInterface;
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
    public function createCase(string $id, DateTimeInterface $expiresAt): DbcoCase
    {
        $values = [
            'id' => $id,
            'expires_at' => $expiresAt->format(\DateTime::ATOM)
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

        return new DbcoCase($id, $expiresAt);
    }
}

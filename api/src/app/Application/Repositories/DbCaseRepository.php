<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Helpers\RandomKeyGenerator;
use App\Application\Helpers\RandomKeyGeneratorInterface;
use App\Application\Models\DbcoCase;
use Exception;
use PDO;

/**
 * Repository.
 */
class DbCaseRepository implements CaseRepository
{
    private PDO $connection;

    private RandomKeyGeneratorInterface $randomKeyGenerator;

    private int $maxKeyGenerationAttempts;

    /**
     * Constructor.
     *
     * @param PDO $connection The database connection
     * @param RandomKeyGeneratorInterface $randomKeyGenerator
     */
    public function __construct(PDO $connection, RandomKeyGeneratorInterface $randomKeyGenerator, int $maxKeyGenerationAttempts)
    {
        $this->connection = $connection;
        $this->randomKeyGenerator = $randomKeyGenerator;
        $this->maxKeyGenerationAttempts = $maxKeyGenerationAttempts;
    }

    /**
     * Insert case row.
     *
     * @return DbcoCase
     * @throws Exception
     */
    public function create(): DbcoCase
    {
        $attempts = 0;
        do {
            $caseId = $this->randomKeyGenerator->generateToken();
            $attempts++;
            if ($this->maxKeyGenerationAttempts !== -1 && $attempts > $this->maxKeyGenerationAttempts) {
                throw new Exception('Error creating an unique case id');
            }
        } while ($this->caseIdExists($caseId));

        do {
            $linkCode = $this->randomKeyGenerator->generateToken();
            $attempts++;
            if ($this->maxKeyGenerationAttempts !== -1 && $attempts > $this->maxKeyGenerationAttempts) {
                throw new Exception('Error creating an unique linkCode');
            }
        } while ($this->linkCodeExists($linkCode));

        $row = [
            'id' =>  $caseId,
            'link_code' =>  $linkCode,
            'link_code_expires_at' => (new \DateTime('-1 hour', new \DateTimeZone('UTC')))->format(\DateTime::ATOM),
        ];

        $sql = "INSERT INTO case (id, link_code, link_code_expires_at) VALUES (
                :id,
                :link_code,
                :link_code_expires_at)";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error preparing case query');
        }
        $stmt->execute($row);
        return new DbcoCase($row['id'], $row['link_code'], $row['link_code_expires_at']);
    }

    protected function caseIdExists(string $caseId): bool
    {
        $values = [
            'id' => $caseId,
        ];
        $sql = "SELECT COUNT(id) as total FROM case WHERE id = :id";
        $sth = $this->connection->prepare($sql);
        $sth->execute($values);
        $total = (int) $sth->fetchColumn(0);
        return $total > 0;
    }

    protected function linkCodeExists(string $linkCode): bool
    {
        $values = [
            'linkCode' => $linkCode,
        ];
        $sql = "SELECT COUNT(linkCode) as total FROM case WHERE linkCode = :linkCode";
        $sth = $this->connection->prepare($sql);
        $sth->execute($values);
        $total = (int) $sth->fetchColumn(0);
        return $total > 0;
    }
}

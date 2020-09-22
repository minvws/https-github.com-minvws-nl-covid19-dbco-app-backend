<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use Psr\Container\ContainerInterface;
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
     * DbCaseRepository constructor.
     *
     * @param \PDO $connection
     * @param \App\Application\Helpers\RandomKeyGeneratorInterface $randomKeyGenerator
     * @param int $maxKeyGenerationAttempts
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
    public function create(string $caseId): DbcoCase
    {
        $attempts = 0;
        do {
            $pairingCode = $this->randomKeyGenerator->generateToken();
            $attempts++;
            if ($this->maxKeyGenerationAttempts !== -1 && $attempts > $this->maxKeyGenerationAttempts) {
                throw new Exception('Error creating an unique pairingCode');
            }
        } while ($this->pairingCodeExists($pairingCode));

        $row = [
            'case_id' =>  $caseId,
            'pairing_code' =>  $pairingCode,
            'pairing_code_expires_at' => (new \DateTime('+1 hour', new \DateTimeZone('UTC')))->format(\DateTime::ATOM),
        ];

        $sql = "INSERT INTO dbco_case (case_id, pairing_code, pairing_code_expires_at) VALUES (
                :case_id,
                :pairing_code,
                :pairing_code_expires_at)";

        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error prepairing case query');
        }
        $stmt->execute($row);
        return new DbcoCase($row['case_id'], $row['pairing_code'], $row['pairing_code_expires_at']);
    }

    protected function pairingCodeExists(string $pairingCode): bool
    {
        $values = [
            'pairing_code' => $pairingCode,
        ];
        $sql = "SELECT COUNT(pairing_code) as total FROM dbco_case WHERE pairing_code = :pairing_code";
        $sth = $this->connection->prepare($sql);
        $sth->execute($values);
        $total = (int) $sth->fetchColumn(0);
        return $total > 0;
    }
}

<?php
namespace App\Application\Services;

use App\Application\Helpers\TokenGenerator;
use App\Application\Managers\TransactionManager;
use App\Application\Models\DbcoCase;
use App\Application\Models\Pairing;
use App\Application\Repositories\CaseRepository;
use App\Application\Repositories\PairingRepository;
use DateTimeInterface;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for registering cases and managing pairings.
 *
 * @package App\Application\Services
 */
class CaseService
{
    /**
     * @var CaseRepository
     */
    private CaseRepository $caseRepository;

    /**
     * @var PairingRepository
     */
    private PairingRepository $pairingRepository;

    /**
     * @var TokenGenerator
     */
    private TokenGenerator $pairingCodeGenerator;

    /**
     * @var int
     */
    private int $pairingCodeTimeToLive;

    /**
     * @var TransactionManager
     */
    private TransactionManager $transactionManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param CaseRepository     $caseRepository
     * @param PairingRepository  $pairingRepository
     * @param TokenGenerator     $pairingCodeGenerator
     * @param int                $pairingCodeTimeToLive
     * @param TransactionManager $transactionManager
     * @param LoggerInterface    $logger
     */
    public function __construct(
        CaseRepository $caseRepository,
        PairingRepository $pairingRepository,
        TokenGenerator $pairingCodeGenerator,
        int $pairingCodeTimeToLive,
        TransactionManager $transactionManager,
        LoggerInterface $logger
    )
    {
        $this->transactionManager = $transactionManager;
        $this->caseRepository = $caseRepository;
        $this->pairingRepository = $pairingRepository;
        $this->pairingCodeGenerator = $pairingCodeGenerator;
        $this->pairingCodeTimeToLive = $pairingCodeTimeToLive;
        $this->logger = $logger;
    }

    /**
     * Create case.
     *
     * @param string            $caseId
     * @param DateTimeInterface $caseExpiresAt
     *
     * @return DbcoCase
     */
    protected function createCase(string $caseId, DateTimeInterface $caseExpiresAt): DbcoCase
    {
        return $this->caseRepository->createCase($caseId, $caseExpiresAt);
    }

    /**
     * Generate unique pairing code.
     *
     * @return string
     */
    protected function generatePairingCode(): string
    {
        do {
            $code = $this->pairingCodeGenerator->generateToken();
        } while ($this->pairingRepository->isActivePairingCode($code));

        return $code;
    }

    /**
     * Create pairing.
     *
     * @param DbcoCase $case
     *
     * @return Pairing
     *
     * @throws Exception
     */
    protected function createPairing(DbcoCase $case): Pairing
    {
        $code = $this->generatePairingCode();
        $codeExpiresAt = new \DateTime('+' . $this->pairingCodeTimeToLive . 's');
        return $this->pairingRepository->createPairing($case->id, $code, $codeExpiresAt);
    }

    /**
     * Registers case and returns pairing information.
     *
     * @param string            $id        Case identifier.
     * @param DateTimeInterface $expiresAt Case expiry.
     *
     * @return Pairing
     *
     * @throws Exception
     */
    public function registerCase(string $id, DateTimeInterface $expiresAt): Pairing
    {
        $this->logger->debug('Register case ' . $id);

        $pairing = $this->transactionManager->run(function () use ($id, $expiresAt) {
            $case = $this->createCase($id, $expiresAt);
            return $this->createPairing($case);
        });

        $this->logger->debug('Created pairing ' . $pairing->id . ' for case ' . $id . ', code: ' . $pairing->code);

        return $pairing;
    }
}

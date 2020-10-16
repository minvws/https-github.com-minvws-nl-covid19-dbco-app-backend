<?php
namespace App\Application\Services;

use App\Application\Helpers\KeyGenerator;
use DateTime;
use DBCO\Application\Models\DbcoCase;
use DBCO\Application\Models\Pairing;
use DBCO\Application\Repositories\PairingRepository;
use DBCO\Application\Repositories\CaseRepository;
use DBCO\Application\Managers\TransactionManager;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for linking devices and cases.
 *
 * @package App\Application\Services
 */
class PairingService
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
     * @var KeyGenerator
     */
    private KeyGenerator $keyGenerator;

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
     * @param PairingRepository  $pairingRepository
     * @param KeyGenerator       $keyGenerator
     * @param TransactionManager $transactionManager
     * @param LoggerInterface    $logger
     */
    public function __construct(
        PairingRepository $pairingRepository,
        KeyGenerator $keyGenerator,
        TransactionManager $transactionManager,
        LoggerInterface $logger
    )
    {
        $this->pairingRepository = $pairingRepository;
        $this->keyGenerator = $keyGenerator;
        $this->transactionManager = $transactionManager;
        $this->logger = $logger;
    }

    /**
     * Get pairing.
     *
     * @param string $code
     *
     * @return Pairing
     *
     * @throws InvalidPairingCodeException
     */
    protected function getPairingByCode(string $code): Pairing
    {
        $pairing = $this->pairingRepository->getPairingByCode($code);
        if ($pairing === null || $pairing->codeExpiresAt < new DateTime()) {
            throw new InvalidPairingCodeException();
        } else {
            return $pairing;
        }
    }

    /**
     * Generate signing key that can be used to sign future requests.
     *
     * @return string
     */
    protected function generateSigningKey(): string
    {
        return $this->keyGenerator->generateKey();
    }

    /**
     * Update pairing.
     *
     * @param Pairing  $pairing
     * @param string[] $fields
     *
     * @throws Exception
     */
    protected function updatePairing(Pairing $pairing, array $fields)
    {
        $this->pairingRepository->updatePairing($pairing, $fields);
    }

    /**
     * Registers case and returns pairing information.
     *
     * @param string $pairingCode
     * @param string $deviceType
     * @param string $deviceName
     *
     * @return Pairing
     *
     * @throws Exception
     */
    public function completePairing(string $pairingCode, string $deviceType, string $deviceName): Pairing
    {
        $this->logger->debug('Complete pairing with code ' . $pairingCode);

        $pairing = $this->transactionManager->run(function () use ($pairingCode, $deviceType, $deviceName) {
            $pairing = $this->getPairingByCode($pairingCode);
            $pairing->code = null;
            $pairing->isPaired = true;
            $pairing->signingKey = $this->generateSigningKey();
            $this->updatePairing($pairing, ['code', 'isPaired', 'signingKey']);
            return $pairing;
        });

        $this->logger->debug('Finished pairing ' . $pairingCode . ' / ' .  $pairing->id . ' for case ' . $pairing->case->id);

        return $pairing;
    }
}

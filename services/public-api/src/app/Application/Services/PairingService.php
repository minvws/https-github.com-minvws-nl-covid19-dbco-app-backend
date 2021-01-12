<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Exceptions\PairingRequestExpiredException;
use DBCO\PublicAPI\Application\Exceptions\PairingRequestNotFoundException;
use DBCO\PublicAPI\Application\Models\Pairing;
use DBCO\PublicAPI\Application\Repositories\PairingRepository;
use DBCO\PublicAPI\Application\Repositories\PairingRequestRepository;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Responsible for linking devices and cases.
 *
 * @package DBCO\PublicAPI\Application\Services
 */
class PairingService
{
    /**
     * @var PairingRequestRepository
     */
    private PairingRequestRepository $pairingRequestRepository;

    /**
     * @var PairingRepository
     */
    private PairingRepository $pairingRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PairingRequestRepository $pairingRequestRepository
     * @param PairingRepository        $pairingRepository
     * @param LoggerInterface          $logger
     */
    public function __construct(
        PairingRequestRepository $pairingRequestRepository,
        PairingRepository $pairingRepository,
        LoggerInterface $logger
    )
    {
        $this->pairingRequestRepository = $pairingRequestRepository;
        $this->pairingRepository = $pairingRepository;
        $this->logger = $logger;
    }

    /**
     * Create pairing.
     *
     * @param string $code Pairing code
     *
     * @return string
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    protected function completePairingRequest(string $code): string
    {
        return $this->pairingRequestRepository->completePairingRequest($code);
    }

    /**
     * Create pairing.
     *
     * @param string $caseUuid
     * @param string $sealedClientPublicKey
     *
     * @return Pairing
     */
    protected function createPairing(string $caseUuid, string $sealedClientPublicKey): Pairing
    {
        return new Pairing($caseUuid, $sealedClientPublicKey);
    }

    /**
     * Store pairing.
     *
     * @param Pairing $pairing
     *
     * @throws Exception
     */
    protected function storePairing(Pairing $pairing)
    {
        $this->pairingRepository->storePairing($pairing);
    }

    /**
     * Registers case and returns pairing information.
     *
     * @param string $pairingCode
     * @param string $sealedClientPublicKey
     *
     * @return Pairing
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function completePairing(string $pairingCode, string $sealedClientPublicKey): Pairing
    {
        $this->logger->debug('Complete pairing with code ' . $pairingCode);

        try {
            $caseUuid = $this->completePairingRequest($pairingCode);
            $pairing = $this->createPairing($caseUuid, $sealedClientPublicKey);
            $this->storePairing($pairing);

            $this->logger->debug('Completed pairing with code ' . $pairingCode . ' for case ' . $caseUuid);
            $this->logger->debug('Case ' . $caseUuid . ' paired');

            return $pairing;
        } catch (Exception $e) {
            $this->logger->alert('Pairing failed for code ' . $pairingCode . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
}

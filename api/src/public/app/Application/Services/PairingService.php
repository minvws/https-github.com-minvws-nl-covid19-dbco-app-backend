<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Models\PairingCase;
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
     * @return PairingCase
     *
     * @throws InvalidPairingCodeException
     */
    protected function completePairingRequest(string $code): PairingCase
    {
        $case = $this->pairingRequestRepository->completePairingRequest($code);
        if ($case === null) {
            throw new InvalidPairingCodeException('Invalid pairing code');
        }

        return $case;
    }

    /**
     * Create pairing.
     *
     * @param PairingCase $case
     * @param string      $sealedClientPublicKey
     *
     * @return Pairing
     */
    protected function createPairing(PairingCase $case, string $sealedClientPublicKey): Pairing
    {
        return new Pairing($case, $sealedClientPublicKey);
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
     * @throws InvalidPairingCodeException
     */
    public function completePairing(string $pairingCode, string $sealedClientPublicKey): Pairing
    {
        $this->logger->debug('Complete pairing with code ' . $pairingCode);

        try {
            $case = $this->completePairingRequest($pairingCode);
            $pairing = $this->createPairing($case, $sealedClientPublicKey);
            $this->storePairing($pairing);

            $this->logger->debug('Completed pairing with code ' . $pairingCode . ' for case ' . $case->id);
            $this->logger->debug('Case ' . $case->id . ' paired');

            return $pairing;
        } catch (Exception $e) {
            $this->logger->alert('Pairing failed for code ' . $pairingCode . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
}

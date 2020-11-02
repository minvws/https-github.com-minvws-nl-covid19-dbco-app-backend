<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Helpers\KeyGenerator;
use DBCO\PublicAPI\Application\Models\PairingCase;
use DBCO\PublicAPI\Application\Models\Pairing;
use DBCO\PublicAPI\Application\Repositories\CaseRepository;
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
     * @var KeyGenerator
     */
    private KeyGenerator $keyGenerator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PairingRequestRepository $pairingRequestRepository
     * @param PairingRepository        $pairingRepository
     * @param KeyGenerator             $keyGenerator
     * @param LoggerInterface          $logger
     */
    public function __construct(
        PairingRequestRepository $pairingRequestRepository,
        PairingRepository $pairingRepository,
        KeyGenerator $keyGenerator,
        LoggerInterface $logger
    )
    {
        $this->pairingRequestRepository = $pairingRequestRepository;
        $this->pairingRepository = $pairingRepository;
        $this->keyGenerator = $keyGenerator;
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
     * Generate signing key that can be used to sign future requests.
     *
     * @return string
     */
    protected function generateSigningKey(): string
    {
        return $this->keyGenerator->generateKey();
    }

    /**
     * Create pairing.
     *
     * @param PairingCase $case
     *
     * @return Pairing
     */
    protected function createPairing(PairingCase $case): Pairing
    {
        $signingKey = $this->generateSigningKey(); // TODO: should be provided by app
        return new Pairing($case, $signingKey);
    }

    /**
     * Store pairing.
     *
     * @param Pairing $pairing
     */
    protected function storePairing(Pairing $pairing)
    {
        $this->pairingRepository->storePairing($pairing);
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
     * @throws InvalidPairingCodeException
     */
    public function completePairing(string $pairingCode, string $deviceType, string $deviceName): Pairing
    {
        $this->logger->debug('Complete pairing with code ' . $pairingCode);

        $case = $this->completePairingRequest($pairingCode);
        $pairing = $this->createPairing($case);
        $this->storePairing($pairing);

        $this->logger->debug('Completed pairing with code ' . $pairingCode . ' for case ' . $case->id);
        $this->logger->debug('Case ' . $case->id . ' paired to ' . $deviceName . '(' . $deviceType . ')');

        return $pairing;
    }
}

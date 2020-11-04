<?php
namespace DBCO\PrivateAPI\Application\Services;

use DBCO\PrivateAPI\Application\Helpers\TokenGenerator;
use DBCO\PrivateAPI\Application\Models\PairingCase;
use DBCO\PrivateAPI\Application\Models\PairingRequest;
use DBCO\PrivateAPI\Application\Repositories\CaseRepository;
use DBCO\PrivateAPI\Application\Repositories\PairingRequestRepository;
use DateTime;
use DateTimeInterface;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Responsible for registering cases and managing pairings.
 *
 * @package DBCO\PrivateAPI\Application\Services
 */
class CaseService
{
    /**
     * @var PairingRequestRepository
     */
    private PairingRequestRepository $pairingRequestRepository;

    /**
     * @var CaseRepository
     */
    private CaseRepository $caseRepository;

    /**
     * @var TokenGenerator
     */
    private TokenGenerator $pairingCodeGenerator;

    /**
     * @var int
     */
    private int $pairingCodeTimeToLive;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PairingRequestRepository $pairingRequestRepository
     * @param CaseRepository           $caseRepository
     * @param TokenGenerator           $pairingCodeGenerator
     * @param int                      $pairingCodeTimeToLive
     * @param LoggerInterface          $logger
     */
    public function __construct(
        PairingRequestRepository $pairingRequestRepository,
        CaseRepository  $caseRepository,
        TokenGenerator $pairingCodeGenerator,
        int $pairingCodeTimeToLive,
        LoggerInterface $logger
    )
    {
        $this->pairingRequestRepository = $pairingRequestRepository;
        $this->caseRepository = $caseRepository;
        $this->pairingCodeGenerator = $pairingCodeGenerator;
        $this->pairingCodeTimeToLive = $pairingCodeTimeToLive;
        $this->logger = $logger;
    }

    /**
     * Create case.
     *
     * @param string            $id
     * @param DateTimeInterface $expiresAt
     *
     * @return PairingCase
     *
     * @throws Exception
     */
    protected function createCase(string $id, DateTimeInterface $expiresAt): PairingCase
    {
        if ($expiresAt <= new DateTime()) {
            throw new RuntimeException('Case has already expired');
        }

        return new PairingCase($id, $expiresAt);
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
        } while ($this->pairingRequestRepository->isActivePairingCode($code));

        return $code;
    }

    /**
     * Create pairing request.
     *
     * @param PairingCase $case
     *
     * @return PairingRequest
     *
     * @throws Exception
     */
    protected function createPairingRequest(PairingCase $case): PairingRequest
    {
        $code = $this->generatePairingCode();
        $codeExpiresAt = min($case->expiresAt, new DateTime('+' . $this->pairingCodeTimeToLive . ' seconds'));
        return new PairingRequest($case, $code, $codeExpiresAt);
    }

    /**
     * Store pairing request.
     *
     * @param PairingRequest $request
     *
     * @throws Exception
     */
    protected function storePairingRequest(PairingRequest $request)
    {
        $this->pairingRequestRepository->storePairingRequest($request);
    }

    /**
     * Registers case and returns pairing request.
     *
     * @param string            $id        Case identifier.
     * @param DateTimeInterface $expiresAt Case expiry.
     *
     * @return PairingRequest
     *
     * @throws Exception
     */
    public function registerCase(string $id, DateTimeInterface $expiresAt): PairingRequest
    {
        $this->logger->debug('Create case ' . $id);
        $case = $this->createCase($id, $expiresAt);

        $this->logger->debug('Create pairing request for case ' . $id);
        $pairingRequest = $this->createPairingRequest($case);

        $this->logger->debug('Store pairing request for case ' . $id . ', code: ' . $pairingRequest->code);
        $this->storePairingRequest($pairingRequest);;

        $this->logger->debug('Stored pairing request for case ' . $id);

        return $pairingRequest;
    }

    /**
     * Store encrypted case payload for client retrieval.
     *
     * @param string $token                Case token.
     * @param string $payload              Encrypted payload.
     * @param DateTimeInterface $expiresAt Data should automatically be wiped after expiry date.
     */
    public function storeCase(string $token, string $payload, DateTimeInterface $expiresAt)
    {
        $this->caseRepository->storeCase($token, $payload, $expiresAt);
    }
}

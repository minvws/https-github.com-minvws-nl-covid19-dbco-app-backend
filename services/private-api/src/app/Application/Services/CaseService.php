<?php
namespace DBCO\PrivateAPI\Application\Services;

use DateTimeZone;
use DBCO\PrivateAPI\Application\Helpers\TokenGenerator;
use DBCO\PrivateAPI\Application\Models\PairingCase;
use DBCO\PrivateAPI\Application\Models\PairingRequest;
use DBCO\PrivateAPI\Application\Repositories\CaseRepository;
use DBCO\PrivateAPI\Application\Repositories\PairingRequestRepository;
use DateTime;
use DateTimeInterface;
use DBCO\Shared\Application\Models\SealedData;
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
    // max attempts for generating an available pairing code
    private const MAX_PAIRING_CODE_ATTEMPTS = 10;

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
    private int $pairingCodeExpiresDelta;

    /**
     * @var int
     */
    private int $pairingCodeExpiredWarningDelta;

    /**
     * @var int
     */
    private int $pairingCodeBlockedDelta;

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
     * @param int                      $pairingCodeExpiresDelta
     * @param int                      $pairingCodeExpiredWarningDelta
     * @param int                      $pairingCodeBlockedDelta
     * @param LoggerInterface          $logger
     */
    public function __construct(
        PairingRequestRepository $pairingRequestRepository,
        CaseRepository  $caseRepository,
        TokenGenerator $pairingCodeGenerator,
        int $pairingCodeExpiresDelta,
        int $pairingCodeExpiredWarningDelta,
        int $pairingCodeBlockedDelta,
        LoggerInterface $logger
    )
    {
        $this->pairingRequestRepository = $pairingRequestRepository;
        $this->caseRepository = $caseRepository;
        $this->pairingCodeGenerator = $pairingCodeGenerator;
        $this->pairingCodeExpiresDelta = $pairingCodeExpiresDelta;
        $this->pairingCodeExpiredWarningDelta = $pairingCodeExpiredWarningDelta;
        $this->pairingCodeBlockedDelta = $pairingCodeBlockedDelta;
        $this->logger = $logger;
    }

    /**
     * Generate unique pairing code.
     *
     * @return string
     */
    protected function generatePairingCode(): string
    {
        for ($i = 0; $i < self::MAX_PAIRING_CODE_ATTEMPTS; $i++) {
            $code = $this->pairingCodeGenerator->generateToken();

            if ($this->pairingRequestRepository->isPairingCodeAvailable($code)) {
                return $code;
            }
        }

        // should not happen in practice, so if this happens, something fishy is going on!
        throw new RuntimeException('Max attempts for generating pairing code reached!');
    }

    /**
     * Create pairing request.
     *
     * @param string $caseUuid
     *
     * @return PairingRequest
     *
     * @throws Exception
     */
    protected function createPairingRequest(string $caseUuid): PairingRequest
    {
        $code = $this->generatePairingCode();
        $tz = new DateTimeZone('UTC');
        $codeExpiresAt = new DateTime('+' . $this->pairingCodeExpiresDelta . ' seconds', $tz);
        $codeExpiredWarningUntil = new DateTime('+' . $this->pairingCodeExpiredWarningDelta . ' seconds', $tz);
        $codeBlockedUntil = new DateTime('+' . $this->pairingCodeBlockedDelta . ' seconds', $tz);
        return new PairingRequest($caseUuid, $code, $codeExpiresAt, $codeExpiredWarningUntil, $codeBlockedUntil);
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
        // first disable any active pairing code that might already exist for this case
        $this->pairingRequestRepository->disableActivePairingCodeForCase($request->caseUuid);

        // now store the new pairing request
        $this->pairingRequestRepository->storePairingRequest($request);
    }

    /**
     * Registers case and returns pairing request.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return PairingRequest
     *
     * @throws Exception
     */
    public function registerCase(string $caseUuid): PairingRequest
    {
        $this->logger->debug('Create pairing request for case ' . $caseUuid);
        $pairingRequest = $this->createPairingRequest($caseUuid);

        $this->logger->debug('Store pairing request for case ' . $caseUuid . ', code: ' . $pairingRequest->code);
        $this->storePairingRequest($pairingRequest);;

        $this->logger->debug('Stored pairing request for case ' . $caseUuid);

        return $pairingRequest;
    }

    /**
     * Store encrypted case payload for client retrieval.
     *
     * @param string            $token      Case token.
     * @param SealedData        $sealedCase Encrypted case.
     * @param DateTimeInterface $expiresAt  Data should automatically be wiped after expiry date.
     */
    public function storeCase(string $token, SealedData $sealedCase, DateTimeInterface $expiresAt)
    {
        $this->caseRepository->storeCase($token, $sealedCase, $expiresAt);
    }
}

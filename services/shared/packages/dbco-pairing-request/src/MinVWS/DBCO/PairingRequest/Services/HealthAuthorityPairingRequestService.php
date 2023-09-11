<?php

namespace MinVWS\DBCO\PairingRequest\Services;

use DateInterval;
use DateTime;
use DateTimeZone;
use MinVWS\DBCO\PairingRequest\Exceptions\CodePoolDepletedException;
use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestExpiredException;
use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestNotFoundException;
use MinVWS\DBCO\PairingRequest\Helpers\CodeGenerator;
use MinVWS\DBCO\PairingRequest\Models\HealthAuthorityPairingRequest;
use MinVWS\DBCO\PairingRequest\Models\IndexPairingRequest;
use MinVWS\DBCO\PairingRequest\Repositories\HealthAuthorityPairingRequestRepository;
use Psr\Log\LoggerInterface;

/**
 * Service for creating and retrieving pairing requests that originate from the index.
 *
 * @package MinVWS\DBCO\PairingRequest\Services
 */
class HealthAuthorityPairingRequestService
{
    /**
     * Number of attempts to generate an, hopefully, available pairing code.
     */
    private const MAX_PAIRING_CODE_ATTEMPTS = 10;

    /**
     * @var HealthAuthorityPairingRequestRepository
     */
    private HealthAuthorityPairingRequestRepository $pairingRequestRepository;

    /**
     * @var CodeGenerator
     */
    private CodeGenerator $codeGenerator;

    /**
     * @var int
     */
    private int $codeBlockedDelta;

    /**
     * @var int
     */
    private int $requestExpiresDelta;

    /**
     * @var int
     */
    private int $requestExpiredWarningDelta;

    /**
     * @var IndexPairingRequestService
     */
    private IndexPairingRequestService $indexPairingRequestService;

    /**
     * @var int
     */
    private int $indexPairingRequestExpiresAdditionalDelta;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param HealthAuthorityPairingRequestRepository $pairingRequestRepository
     * @param CodeGenerator                           $codeGenerator
     * @param int                                     $codeBlockedDelta
     * @param int                                     $requestExpiresDelta
     * @param int                                     $requestExpiredWarningDelta
     * @param IndexPairingRequestService              $indexPairingRequestService
     * @param int                                     $indexPairingRequestExpiresAdditionalDelta
     * @param LoggerInterface                         $logger
     */
    public function __construct(
        HealthAuthorityPairingRequestRepository $pairingRequestRepository,
        CodeGenerator $codeGenerator,
        int $codeBlockedDelta,
        int $requestExpiresDelta,
        int $requestExpiredWarningDelta,
        IndexPairingRequestService $indexPairingRequestService,
        int $indexPairingRequestExpiresAdditionalDelta,
        LoggerInterface $logger
    ) {
        $this->pairingRequestRepository = $pairingRequestRepository;
        $this->codeGenerator = $codeGenerator;
        $this->codeBlockedDelta = $codeBlockedDelta;
        $this->requestExpiresDelta = $requestExpiresDelta;
        $this->requestExpiredWarningDelta = $requestExpiredWarningDelta;
        $this->indexPairingRequestService = $indexPairingRequestService;
        $this->indexPairingRequestExpiresAdditionalDelta = $indexPairingRequestExpiresAdditionalDelta;
        $this->logger = $logger;
    }

    /**
     * Returns an available pairing code that is still available and blocks it.
     *
     * @return string
     *
     * @throws CodePoolDepletedException
     */
    private function generatePairingRequestCode(): string
    {
        for ($i = 0; $i < self::MAX_PAIRING_CODE_ATTEMPTS; $i++) {
            $code = $this->codeGenerator->generateCode();

            if (
                $this->pairingRequestRepository->isPairingRequestCodeAvailable($code) &&
                $this->pairingRequestRepository->blockPairingRequestCode($code, $this->codeBlockedDelta)
            ) {
                return $code;
            }
        }

        throw new CodePoolDepletedException();
    }

    /**
     * Registers case and returns pairing request.
     *
     * @param string $caseUuid Case identifier.
     * @param string|null $code Index pairing request code.
     *
     * @return HealthAuthorityPairingRequest
     *
     * @throws PairingRequestNotFoundException
     * @throws PairingRequestExpiredException
     * @throws CodePoolDepletedException
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function createPairingRequest(string $caseUuid, ?string $code): HealthAuthorityPairingRequest
    {
        if ($code !== null) {
            $this->logger->debug(sprintf('Create pairing request for case %s (origin: index, code: %s)', $caseUuid, $code));

            $indexPairingRequest = $this->indexPairingRequestService->getPairingRequestByCode($code);

            // add some extra time for finishing the pairing request, this makes sure that if the health authority
            // entered the code just before the expiration the client still has enough time to retrieve this new status
            $additionalExpiresDelta = new DateInterval('PT' . $this->indexPairingRequestExpiresAdditionalDelta . 'S');
            $indexPairingRequest->expiresAt = $indexPairingRequest->expiresAt->add($additionalExpiresDelta);
            $expiresAt = $indexPairingRequest->expiresAt;
            $expiredWarningUntil = $indexPairingRequest->expiredWarningUntil;
        } else {
            $this->logger->debug(sprintf('Create pairing request for case %s (origin: health authority)', $caseUuid));

            $tz = new DateTimeZone('UTC');
            $expiresAt = new DateTime('+' . $this->requestExpiresDelta . ' seconds', $tz);
            $expiredWarningUntil = new DateTime('+' . $this->requestExpiredWarningDelta . ' seconds', $tz);
        }

        $code = $this->generatePairingRequestCode();
        $request = new HealthAuthorityPairingRequest($caseUuid, $code, $expiresAt, $expiredWarningUntil);

        // remove previous pairing request for this case (if it still exists)
        $oldRequest = $this->pairingRequestRepository->getPairingRequestByCase($caseUuid);
        if ($oldRequest !== null) {
            $this->pairingRequestRepository->deletePairingRequest($oldRequest);
        }

        $this->logger->debug('Store pairing request for case ' . $caseUuid . ', code: ' . $request->code);
        $this->pairingRequestRepository->storePairingRequest($request);

        if (isset($indexPairingRequest)) {
            $indexPairingRequest->code = null;
            $indexPairingRequest->status = IndexPairingRequest::STATUS_COMPLETED;
            $indexPairingRequest->healthAuthorityPairingRequest = $request;
            $this->indexPairingRequestService->updatePairingRequest($indexPairingRequest);
        }

        $this->logger->debug('Stored pairing request for case ' . $caseUuid);

        return $request;
    }

    /**
     * Complete pairing request.
     *
     * @param string $code Pairing request code.
     *
     * @return string Case UUID.
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function completePairingRequest(string $code): string
    {
        $request = $this->pairingRequestRepository->getPairingRequestByCode($code);
        if ($request === null) {
            throw new PairingRequestNotFoundException();
        }

        // one-time operation, so delete the pairing request
        $this->pairingRequestRepository->deletePairingRequest($request);

        // an expired request is not automatically deleted so we can show a one-time warning
        // that is has expired, unless the warning period expired as well
        $now = new DateTime();
        if ($request->expiresAt < $now && $request->expiredWarningUntil > $now) {
            throw new PairingRequestExpiredException();
        } elseif ($request->expiresAt < $now) {
            throw new PairingRequestNotFoundException();
        }

        return $request->caseUuid;
    }
}

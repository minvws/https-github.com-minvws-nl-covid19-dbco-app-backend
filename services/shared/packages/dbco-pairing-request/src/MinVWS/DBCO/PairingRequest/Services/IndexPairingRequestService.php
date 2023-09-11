<?php

namespace MinVWS\DBCO\PairingRequest\Services;

use DateTime;
use DateTimeZone;
use MinVWS\DBCO\PairingRequest\Exceptions\CodePoolDepletedException;
use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestExpiredException;
use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestNotFoundException;
use MinVWS\DBCO\PairingRequest\Helpers\CodeGenerator;
use MinVWS\DBCO\PairingRequest\Models\IndexPairingRequest;
use MinVWS\DBCO\PairingRequest\Repositories\IndexPairingRequestRepository;
use Ramsey\Uuid\Uuid;

/**
 * Service for creating and retrieving pairing requests that originate from the index.
 *
 * @package MinVWS\DBCO\PairingRequest\Services
 */
class IndexPairingRequestService
{
    /**
     * Number of attempts to generate an, hopefully, available pairing code.
     */
    private const MAX_PAIRING_CODE_ATTEMPTS = 10;

    /**
     * @var IndexPairingRequestRepository
     */
    private IndexPairingRequestRepository $pairingRequestRepository;

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
     * Constructor.
     *
     * @param IndexPairingRequestRepository $pairingRequestRepository
     * @param CodeGenerator                 $codeGenerator
     * @param int                           $codeBlockedDelta
     * @param int                           $requestExpiresDelta
     * @param int                           $requestExpiredWarningDelta
     */
    public function __construct(
        IndexPairingRequestRepository $pairingRequestRepository,
        CodeGenerator $codeGenerator,
        int $codeBlockedDelta,
        int $requestExpiresDelta,
        int $requestExpiredWarningDelta
    ) {
        $this->pairingRequestRepository = $pairingRequestRepository;
        $this->codeGenerator = $codeGenerator;
        $this->codeBlockedDelta = $codeBlockedDelta;
        $this->requestExpiresDelta = $requestExpiresDelta;
        $this->requestExpiredWarningDelta = $requestExpiredWarningDelta;
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
     * Generate pairing request token.
     *
     * @return string
     */
    private function generatePairingRequestToken(): string
    {
        return hash('sha256', Uuid::uuid4());
    }

    /**
     * Creates a new pairing request on behalf of the index.
     *
     * @return IndexPairingRequest
     *
     * @throws CodePoolDepletedException
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function createIndexPairingRequest(): IndexPairingRequest
    {
        $code = $this->generatePairingRequestCode();
        $token = $this->generatePairingRequestToken();
        $tz = new DateTimeZone('UTC');
        $expiresAt = new DateTime('+' . $this->requestExpiresDelta . ' seconds', $tz);
        $expiredWarningUntil = new DateTime('+' . $this->requestExpiredWarningDelta . ' seconds', $tz);
        $request = new IndexPairingRequest($token, IndexPairingRequest::STATUS_PENDING, $code, $expiresAt, $expiredWarningUntil);
        $this->pairingRequestRepository->storePairingRequest($request);
        return $request;
    }

    /**
     * Retrieve the pairing request by code.
     *
     * This is normally called from the health authority portal.
     *
     * @param string $code
     *
     * @return IndexPairingRequest
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function getPairingRequestByCode(string $code): IndexPairingRequest
    {
        $request = $this->pairingRequestRepository->getPairingRequestByCode($code);
        if ($request === null) {
            throw new PairingRequestNotFoundException();
        }

        $now = new DateTime();

        // an expired request is not automatically deleted so we can show a warning
        // that is has expired, unless the warning period expired as well
        if ($request->expiresAt < $now) {
            if ($request->expiredWarningUntil > $now) {
                // don't delete the pairing request yet, so the index can also get the expired warning
                throw new PairingRequestExpiredException();
            } else {
                // expired warning also expired, so delete the request
                $this->pairingRequestRepository->deletePairingRequest($request);
                throw new PairingRequestNotFoundException();
            }
        }

        return $request;
    }

    /**
     * Retrieve the pairing request by token.
     *
     * This is normally called from the client.
     *
     * @param string $token
     *
     * @return IndexPairingRequest
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function getPairingRequestByToken(string $token): IndexPairingRequest
    {
        $request = $this->pairingRequestRepository->getPairingRequestByToken($token);
        if ($request === null) {
            throw new PairingRequestNotFoundException();
        }

        $now = new DateTime();

        // an expired request is not automatically deleted so we can show a one-time warning
        // that is has expired, unless the warning period expired as well
        if ($request->expiresAt < $now) {
            // always delete if expired
            $this->pairingRequestRepository->deletePairingRequest($request);

            if ($request->expiredWarningUntil > $now) {
                throw new PairingRequestExpiredException();
            } else {
                throw new PairingRequestNotFoundException();
            }
        }

        return $request;
    }

    /**
     * Update pairing request.
     *
     * @param IndexPairingRequest $request
     */
    public function updatePairingRequest(IndexPairingRequest $request): void
    {
        $this->pairingRequestRepository->storePairingRequest($request);
    }
}

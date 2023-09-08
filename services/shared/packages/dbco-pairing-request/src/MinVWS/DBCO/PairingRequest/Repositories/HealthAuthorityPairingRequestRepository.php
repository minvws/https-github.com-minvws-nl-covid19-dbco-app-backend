<?php

declare(strict_types=1);

namespace MinVWS\DBCO\PairingRequest\Repositories;

use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestExpiredException;
use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestNotFoundException;
use MinVWS\DBCO\PairingRequest\Models\HealthAuthorityPairingRequest;

/**
 * Manage health authority pairing requests.
 */
interface HealthAuthorityPairingRequestRepository
{
    /**
     * Check if the given pairing request code is available.
     *
     * @param string $code
     *
     * @return bool
     */
    public function isPairingRequestCodeAvailable(string $code): bool;

    /**
     * Block pairing request code.
     *
     * @param string $code
     * @param int    $ttl
     *
     * @return bool
     */
    public function blockPairingRequestCode(string $code, int $ttl): bool;

    /**
     * Retrieve pairing request by code.
     *
     * @param string $code
     *
     * @return HealthAuthorityPairingRequest The pairing request.
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function getPairingRequestByCode(string $code): ?HealthAuthorityPairingRequest;

    /**
     * Retrieve pairing request by case.
     *
     * @param string $caseUuid
     *
     * @return HealthAuthorityPairingRequest The pairing request.
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function getPairingRequestByCase(string $caseUuid): ?HealthAuthorityPairingRequest;

    /**
     * Store / update pairing request.
     *
     * @param HealthAuthorityPairingRequest $request
     */
    public function storePairingRequest(HealthAuthorityPairingRequest $request): void;

    /**
     * Deletes the given pairing request.
     *
     * @param HealthAuthorityPairingRequest $request
     */
    public function deletePairingRequest(HealthAuthorityPairingRequest $request): void;
}

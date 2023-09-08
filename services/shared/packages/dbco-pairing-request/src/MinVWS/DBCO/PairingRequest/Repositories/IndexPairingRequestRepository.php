<?php

declare(strict_types=1);

namespace MinVWS\DBCO\PairingRequest\Repositories;

use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestExpiredException;
use MinVWS\DBCO\PairingRequest\Exceptions\PairingRequestNotFoundException;
use MinVWS\DBCO\PairingRequest\Models\IndexPairingRequest;

/**
 * Manage index pairing requests.
 */
interface IndexPairingRequestRepository
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
     * @return IndexPairingRequest The pairing request.
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function getPairingRequestByCode(string $code): ?IndexPairingRequest;

    /**
     * Retrieve pairing request by token.
     *
     * @param string $token
     *
     * @return IndexPairingRequest The pairing request.
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function getPairingRequestByToken(string $token): ?IndexPairingRequest;

    /**
     * Store / update pairing request.
     *
     * @param IndexPairingRequest $request
     */
    public function storePairingRequest(IndexPairingRequest $request): void;

    /**
     * Deletes the given pairing request.
     *
     * @param IndexPairingRequest $request
     */
    public function deletePairingRequest(IndexPairingRequest $request): void;
}

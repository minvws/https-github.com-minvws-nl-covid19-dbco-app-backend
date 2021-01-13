<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Repositories;

use DBCO\PrivateAPI\Application\Models\PairingRequest;
use Exception;

/**
 * Used to createCase and retrieve pairing information between case and app.
 *
 * @package DBCO\PrivateAPI\Application\Repositories
 */
interface PairingRequestRepository
{
    /**
     * Check if the given pairing code is available for use.
     *
     * @param string $code
     *
     * @return bool Is pairing code available?
     */
    public function isPairingCodeAvailable(string $code): bool;

    /**
     * Disable active pairing code (if any) for the given case.
     *
     * If a valid pairing code exists it will be disabled until it is returned
     * to the pool of valid pairing codes again.
     *
     * @param string $caseUuid
     *
     * @return mixed
     */
    public function disableActivePairingCodeForCase(string $caseUuid);

    /**
     * Store a new pairing request.
     *
     * @param PairingRequest $request
     *
     * @throws Exception
     */
    public function storePairingRequest(PairingRequest $request);
}

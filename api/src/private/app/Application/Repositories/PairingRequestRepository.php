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
     * Check if a pairing request exists and is active.
     *
     * @param string $code
     *
     * @return bool Is pairing request active?
     */
    public function isActivePairingCode(string $code): bool;

    /**
     * Create a new pairing request.
     *
     * @param PairingRequest $request
     *
     * @throws Exception
     */
    public function storePairingRequest(PairingRequest $request);
}

<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Exceptions\PairingException;
use DBCO\Worker\Application\Models\PairingRequest;
use DBCO\Worker\Application\Models\PairingResponse;

/**
 * Health authority pairing gateway.
 *
 * @package App\Application\Repositories
 */
interface HealthAuthorityPairingRepository
{
    /**
     * Register completed pairing with the health authority.
     *
     * @param PairingRequest $request
     *
     * @return PairingResponse
     *
     * @throws PairingException
     */
    public function completePairing(PairingRequest $request): PairingResponse;
}

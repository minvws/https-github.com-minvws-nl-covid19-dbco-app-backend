<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\Pairing;

/**
 * Used to store pairings.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface PairingRepository
{
    /**
     * Create pairing.
     *
     * @param Pairing $pairing
     */
    public function storePairing(Pairing $pairing);
}

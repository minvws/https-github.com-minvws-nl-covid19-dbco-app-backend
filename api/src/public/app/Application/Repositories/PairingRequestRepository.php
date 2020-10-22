<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\PairingCase;

/**
 * Used to complete a pairing request.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface PairingRequestRepository
{
    /**
     * Delete the pairing request and return the case it belonged to.
     *
     * @param string $code
     *
     * @return PairingCase|null The case this pairing request belonged to. Null if it doesn't exist or expired.
     */
    public function completePairingRequest(string $code): ?PairingCase;
}

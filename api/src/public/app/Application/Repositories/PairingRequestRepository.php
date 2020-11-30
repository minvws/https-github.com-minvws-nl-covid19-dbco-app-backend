<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Exceptions\PairingRequestExpiredException;
use DBCO\PublicAPI\Application\Exceptions\PairingRequestNotFoundException;

/**
 * Used to complete a pairing request.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface PairingRequestRepository
{
    /**
     * Delete the pairing request and return the case UUID.
     *
     * @param string $code
     *
     * @return string The case UUID for the pairing request.
     *
     * @throws PairingRequestExpiredException
     * @throws PairingRequestNotFoundException
     */
    public function completePairingRequest(string $code): string;
}

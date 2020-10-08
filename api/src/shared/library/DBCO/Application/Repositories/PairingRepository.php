<?php
declare(strict_types=1);

namespace DBCO\Application\Repositories;

use DateTimeInterface;
use DBCO\Application\Models\Pairing;
use Exception;

/**
 * Used to createCase and retrieve pairing information between case and app.
 *
 * @package DBCO\Application\Repositories
 */
interface PairingRepository
{
    /**
     * Retrieve a pairing by code.
     *
     * This method does not check if the pairing is still active!
     *
     * @param string $code
     *
     * @return Pairing|null Pairing or null if it does not exist.
     */
    public function getPairingByCode(string $code): ?Pairing;

    /**
     * Create a new pairing
     *
     * @param Pairing $pairing
     *
     * @throws Exception
     */
    public function createPairing(Pairing $pairing);


    /**
     * Update an existing pairing.
     *
     * @param Pairing  $pairing
     * @param string[] $fields
     *
     * @throws Exception
     */
    public function updatePairing(Pairing $pairing, array $fields);

    /**
     * Delete expired pairing codes before.
     *
     * @param DateTimeInterface $expiresAtBefore
     */
    public function deletePairingsWithExpiresAtBefore(DateTimeInterface $expiresAtBefore);

    /**
     * Delete expired pairing code (if exists).
     *
     * @param string            $code
     * @param DateTimeInterface $expiresAtBefore
     */
    public function deletePairingWithCodeAndExpiresAtBefore(string $code, DateTimeInterface $expiresAtBefore);
}

<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Repositories;

use DateTimeInterface;

/**
 * Used for storing case details for later retrieval by the client.
 *
 * @package DBCO\PrivateAPI\Application\Repositories
 */
interface CaseRepository
{
    /**
     * Store the given encrypted payload for the given case token.
     *
     * @param string            $token     Case token.
     * @param string            $payload   Encrypted payload.
     * @param DateTimeInterface $expiresAt Data expires at the given time.
     */
    public function storeCase(string $token, string $payload, DateTimeInterface $expiresAt);
}

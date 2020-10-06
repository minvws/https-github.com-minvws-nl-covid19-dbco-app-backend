<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Models\Pairing;
use DateTimeInterface;

/**
 * Used to createCase and retrieve pairing information between case and app.
 *
 * @package App\Application\Repositories
 */
interface PairingRepository
{
    /**
     * Create a new pairing
     *
     * @param string            $caseId
     * @param string            $code
     * @param DateTimeInterface $codeExpiresAt
     *
     * @return Pairing
     */
    public function createPairing(string $caseId, string $code, DateTimeInterface $codeExpiresAt): Pairing;

    /**
     * Check if a pairing code is active.
     *
     * @param string $code
     *
     * @return bool
     */
    public function isActivePairingCode(string $code): bool;
}

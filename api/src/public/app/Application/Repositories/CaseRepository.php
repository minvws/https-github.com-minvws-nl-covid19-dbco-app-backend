<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\SealedCase;
use DBCO\Shared\Application\Models\SealedData;

/**
 * Used for syncing case details.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface CaseRepository
{
    /**
     * Returns the case and its task list.
     *
     * @param string $token Case token.
     *
     * @return SealedCase
     */
    public function getCase(string $token): SealedCase;

    /**
     * Submit case and its tasks.
     *
     * @param string     $token
     * @param SealedCase $sealedCase
     *
     * @return void
     */
    public function submitCase(string $token, SealedCase $sealedCase): void;
}

<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\Shared\Application\Models\SealedData;

/**
 * Used for submitting case results to the health authority.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface CaseSubmitRepository
{
    /**
     * Submit case and its tasks.
     *
     * @param string     $token
     * @param SealedData $sealedCase
     *
     * @return void
     */
    public function submitCase(string $token, SealedData $sealedCase): void;
}

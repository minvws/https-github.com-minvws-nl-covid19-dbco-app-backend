<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\Shared\Application\Models\SealedData;

/**
 * Used for retrieving case details.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface CaseRepository
{
    /**
     * Check if the given token resolves to a case.
     *
     * @param string $token
     *
     * @return bool
     */
    public function caseExists(string $token): bool;

    /**
     * Returns the case and its task list.
     *
     * @param string $token Case token.
     *
     * @return SealedData|null
     */
    public function getCase(string $token): ?SealedData;
}

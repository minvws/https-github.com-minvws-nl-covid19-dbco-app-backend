<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\CovidCase;

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
     * @param string $caseId Case identifier.
     *
     * @return CovidCase
     */
    public function getCase(string $caseId): CovidCase;

    /**
     * Submit case and its tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCase(string $caseId, string $body): void;
}

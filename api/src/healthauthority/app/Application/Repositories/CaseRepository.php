<?php
namespace App\Application\Repositories;

use App\Application\Models\CovidCase;

/**
 * Used for retrieving case specific tasks.
 *
 * @package App\Application\Repositories
 */
interface CaseRepository
{
    /**
     * Returns the case task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CovidCase
     */
    public function getCase(string $caseId): CovidCase;

    /**
     * Submit case tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCase(string $caseId, string $body): void;
}

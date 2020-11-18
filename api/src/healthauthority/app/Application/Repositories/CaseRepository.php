<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;

/**
 * Used for retrieving case specific tasks.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
interface CaseRepository
{
    /**
     * Check if a case exists.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return bool
     */
    public function caseExists(string $caseUuid): bool;

    /**
     * Returns the case task list.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return CovidCase|null
     */
    public function getCase(string $caseUuid): ?CovidCase;

    /**
     * Store case answers.
     *
     * @param CovidCase $case
     *
     * @return void
     */
    public function storeCaseAnswers(CovidCase $case): void;
}

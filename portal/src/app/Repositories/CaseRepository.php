<?php

namespace App\Repositories;

use App\Models\CovidCase;
use Illuminate\Support\Collection;

interface CaseRepository
{
    /**
     * Returns the case and its task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CovidCase The case (or null if not found)
     */
    public function getCase(string $caseId): ?CovidCase;

    /**
     * Returns all the cases of the current user
     * @return Collection
     */
    public function myCases(): Collection;

    /**
     * Create a new, empty case in draft status.
     *
     * @return CovidCase
     */
    public function createDraftCase(): CovidCase;

    /**
     * Update case.
     *
     * @param CovidCase $case Case entity
     */
    public function updateCase(CovidCase $case);

    /**
     * @param CovidCase $case
     * @return bool True if the currently logged in user is the owner of this case.
     */
    public function isOwner(CovidCase $case): bool;
}

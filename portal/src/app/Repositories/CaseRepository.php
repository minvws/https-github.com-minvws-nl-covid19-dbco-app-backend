<?php

namespace App\Repositories;

use App\Models\BCOUser;
use App\Models\CovidCase;
use Illuminate\Support\Collection;

interface CaseRepository
{
    /**
     * Returns the case
     *
     * @param string $caseUuid Case identifier.
     *
     * @return CovidCase The case (or null if not found)
     */
    public function getCase(string $caseUuid): ?CovidCase;

    /**
     * Returns all the cases of a user
     * @return Collection
     */
    public function getCasesByAssignedUser(BCOUser $user): Collection;

    /**
     * Retrusns all cases of a user's organisation
     * @param array $organisations
     * @return Collection
     */
    public function getCasesByOrganisation(BCOUser $user): Collection;

    /**
     * Create a new, empty case
     *
     * @return CovidCase
     */
    public function createCase(BCOUser $owner, string $initialStatus): CovidCase;

    /**
     * Update case.
     *
     * @param CovidCase $case Case entity
     */
    public function updateCase(CovidCase $case);

}

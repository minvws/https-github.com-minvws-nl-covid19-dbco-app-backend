<?php

namespace App\Repositories;

use App\Models\BCOUser;
use App\Models\CovidCase;
use DateTimeImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Jenssegers\Date\Date;


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
     * @return LengthAwarePaginator
     */
    public function getCasesByAssignedUser(BCOUser $user): LengthAwarePaginator;

    /**
     * Retrusns all cases of a user's organisation
     * @param array $organisations
     * @return LengthAwarePaginator
     */
    public function getCasesByOrganisation(BCOUser $user): LengthAwarePaginator;

    /**
     * Create a new, empty case
     *
     * @return CovidCase
     */
    public function createCase(DBCOUser $owner, CovidCase $case): bool;

    /**
     * Update case.
     *
     * @param CovidCase $case Case entity
     */
    public function updateCase(CovidCase $case): bool;

    /**
     * @param CovidCase $case
     * @param DateTimeImmutable $windowExpiresAt
     * @param DateTimeImmutable $pairingExpiresAt
     * @return mixed
     */
    public function setExpiry(CovidCase $case, DateTimeImmutable $windowExpiresAt, DateTimeImmutable $pairingExpiresAt);

}

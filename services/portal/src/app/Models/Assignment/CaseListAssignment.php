<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;

/**
 * Assignment that modifies the assigned case list.
 */
class CaseListAssignment implements Assignment
{
    private CaseList $caseList;

    public function __construct(CaseList $caseList)
    {
        $this->caseList = $caseList;
    }

    public function getCaseList(): CaseList
    {
        return $this->caseList;
    }

    public function isValidForSelectedOrganisation(EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool
    {
        return $this->caseList->organisation_uuid === $selectedOrganisation->uuid;
    }

    public function isValidForCaseWithSelectedOrganisation(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool
    {
        if ($this->caseList->is_queue && $case->isWaitingForApproval()) {
            return false;
        }

        if ($case->assigned_case_list_uuid !== null) {
            return true;
        }

        if (!$this->caseList->is_queue && $this->caseList->uuid === $case->assigned_case_list_uuid) {
            return false;
        }

        if ($case->assigned_organisation_uuid === null && $case->organisation_uuid === $selectedOrganisation->uuid) {
            // owner organisation  can only change case list if not assigned to a different organisation
            return !$validateFull || $this->isValidForSelectedOrganisation($selectedOrganisation);
        }

        if ($case->assigned_organisation_uuid === $selectedOrganisation->uuid) {
            // assigned organisation can change case list
            return !$validateFull || $this->isValidForSelectedOrganisation($selectedOrganisation);
        }

        return false;
    }

    public function applyToCase(EloquentCase $case): void
    {
        $case->assigned_case_list_uuid = $this->caseList->uuid;

        if (isset($case->assigned_user_uuid) && $this->caseList->is_queue) {
            $case->assigned_user_uuid = null;
        }
    }
}

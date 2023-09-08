<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;

/**
 * Assignment that modifies the assigned case list to null.
 */
class NullCaseListAssignment implements Assignment
{
    public function isValidForSelectedOrganisation(
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull = true,
        ?Cache $cache = null,
    ): bool {
        return true;
    }

    public function isValidForCaseWithSelectedOrganisation(
        EloquentCase $case,
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull = true,
        ?Cache $cache = null,
    ): bool {
        if ($case->assigned_organisation_uuid === null && $case->organisation_uuid === $selectedOrganisation->uuid) {
            // owner organisation can only change case list if not assigned to a different organisation
            return true;
        }

        // assigned organisation can change case list
        return $case->assigned_organisation_uuid === $selectedOrganisation->uuid;
    }

    public function applyToCase(EloquentCase $case): void
    {
        $case->assigned_case_list_uuid = null;
    }
}

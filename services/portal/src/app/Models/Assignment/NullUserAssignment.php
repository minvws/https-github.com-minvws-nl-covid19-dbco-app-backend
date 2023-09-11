<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;

/**
 * Assignment that changes the assigned user to null.
 */
class NullUserAssignment implements Assignment
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
        if ($case->isWaitingForApproval()) {
            return true;
        }

        if ($case->assigned_organisation_uuid === null && $case->organisation_uuid === $selectedOrganisation->uuid) {
            // owner organisation can only change user if not assigned to a different organisation
            return true;
        }

            // assigned organisation can change user
        return $case->assigned_organisation_uuid === $selectedOrganisation->uuid;
    }

    public function applyToCase(EloquentCase $case): void
    {
        $case->assigned_user_uuid = null;
    }
}

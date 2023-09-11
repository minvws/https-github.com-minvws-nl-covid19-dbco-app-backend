<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Helpers\FeatureFlagHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;

/**
 * Assignment that modifies the assigned organisation to null.
 */
class NullOrganisationAssignment implements Assignment
{
    public function isValidForSelectedOrganisation(
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull = true,
        ?Cache $cache = null,
    ): bool {
        return FeatureFlagHelper::isEnabled('outsourcing_enabled');
    }

    public function isValidForCaseWithSelectedOrganisation(
        EloquentCase $case,
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull = true,
        ?Cache $cache = null,
    ): bool {
        // Case is assigned to same Organisation as the Organisation of the User
        if ($case->assigned_organisation_uuid === $selectedOrganisation->uuid) {
            return $this->isValidForSelectedOrganisation($selectedOrganisation);
        }

        // Case is owned by the selected Organisation, is currently assigned to an Organisation but not assigned to a User
        if (
            $case->organisation_uuid === $selectedOrganisation->uuid &&
            $case->assigned_organisation_uuid !== null &&
            $case->assigned_user_uuid === null
        ) {
            return $this->isValidForSelectedOrganisation($selectedOrganisation);
        }

        return false;
    }

    public function applyToCase(EloquentCase $case): void
    {
        $case->assigned_organisation_label = null;
        $case->assigned_organisation_uuid = null;
        $case->assigned_case_list_uuid = null;
        $case->assigned_user_uuid = null;
    }
}

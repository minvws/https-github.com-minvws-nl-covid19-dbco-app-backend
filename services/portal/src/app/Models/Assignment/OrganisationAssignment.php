<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Helpers\FeatureFlagHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;

/**
 * Assignment that modifies the assigned organisation.
 */
class OrganisationAssignment implements Assignment
{
    private EloquentOrganisation $organisation;

    public function __construct(EloquentOrganisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getOrganisation(): EloquentOrganisation
    {
        return $this->organisation;
    }

    public function isValidForSelectedOrganisation(EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool
    {
        if (FeatureFlagHelper::isDisabled('outsourcing_enabled')) {
            return false;
        }

        if (
            $this->organisation->type === OrganisationType::regionalGGD()
            && FeatureFlagHelper::isDisabled('outsourcing_to_regional_ggd_enabled')
        ) {
            return false;
        }

        if ($selectedOrganisation->isOutsourceOrganisation()) {
            return false;
        }

        return !$validateFull
            || (
                $selectedOrganisation->isOrganisationThatOutsourcesTo($this->organisation)
                && $this->organisation->is_available_for_outsourcing
            );
    }

    public function isValidForCaseWithSelectedOrganisation(
        EloquentCase $case,
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull = true,
        ?Cache $cache = null,
    ): bool {
        if ($case->isWaitingForApproval()) {
            // Waiting for approval cannot be assigned to an organisation. But it is possible to return it to the owner organisation (null)
            return false;
        }

        if ($case->organisation_uuid === $selectedOrganisation->uuid && $case->assigned_organisation_uuid !== null) {
            return false; // owner organisation can't change organisation as long as it has been assigned to a different organisation
        }

        if ($case->organisation_uuid === $selectedOrganisation->uuid) {
            return !$validateFull || $this->isValidForSelectedOrganisation(
                $selectedOrganisation,
            ); // owner organisation can re-assign to a partner
        }

        return false;
    }

    public function applyToCase(EloquentCase $case): void
    {
        if ($case->assigned_organisation_uuid !== $this->organisation->uuid) {
            $case->assigned_organisation_label = null;
        }

        $case->assigned_organisation_uuid = $this->organisation->uuid;
        $case->assigned_case_list_uuid = null;
        $case->assigned_user_uuid = null;
    }
}

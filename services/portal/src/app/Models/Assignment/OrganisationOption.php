<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use MinVWS\Codable\EncodingContainer;

/**
 * @extends AssignmentOption<OrganisationAssignment>
 */
class OrganisationOption extends AssignmentOption
{
    public function __construct(OrganisationAssignment $assignment)
    {
        parent::__construct($assignment);
    }

    public function getOrganisation(): EloquentOrganisation
    {
        return $this->getAssignment()->getOrganisation();
    }

    public function getLabel(): string
    {
        return $this->getOrganisation()->name;
    }

    public function encode(EncodingContainer $container): void
    {
        parent::encode($container);

        $container->assignmentType = 'organisation';
        $container->assignment->assignedOrganisationUuid = $this->isSelected() ? null : $this->getOrganisation()->uuid;
    }

    protected function isSelectedForCase(EloquentCase $case): bool
    {
        return $case->assigned_organisation_uuid === $this->getOrganisation()->uuid;
    }
}

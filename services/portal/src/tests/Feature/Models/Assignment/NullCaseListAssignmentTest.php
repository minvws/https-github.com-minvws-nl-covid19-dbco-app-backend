<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Assignment;

use App\Models\Assignment\NullCaseListAssignment;
use Tests\Feature\FeatureTestCase;

class NullCaseListAssignmentTest extends FeatureTestCase
{
    public function testIsValidForSelectedOrganisation(): void
    {
        $organisation = $this->createOrganisation();

        $nullCaseListAssignment = new NullCaseListAssignment();
        $result = $nullCaseListAssignment->isValidForSelectedOrganisation($organisation);

        $this->assertTrue($result);
    }

    public function testIsValidForCaseWithSelectedOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'assigned_organisation_uuid' => null,
        ]);

        $nullCaseListAssignment = new NullCaseListAssignment();
        $result = $nullCaseListAssignment->isValidForCaseWithSelectedOrganisation($case, $organisation, $this->faker->boolean);

        $this->assertTrue($result);
    }

    public function testIsValidForCaseWithSelectedOrganisationButNotAssignedToOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCase([
            'assigned_organisation_uuid' => null,
        ]);

        $nullCaseListAssignment = new NullCaseListAssignment();
        $result = $nullCaseListAssignment->isValidForCaseWithSelectedOrganisation($case, $organisation, $this->faker->boolean);

        $this->assertFalse($result);
    }

    public function testIsValidForCaseWithSelectedOrganisationButHasOrganisation(): void
    {
        $organisation1 = $this->createOrganisation();
        $organisation2 = $this->createOrganisation();
        $case = $this->createCase([
            'organisation_uuid' => $organisation1->uuid,
            'assigned_organisation_uuid' => $organisation2->uuid,
        ]);

        $nullCaseListAssignment = new NullCaseListAssignment();
        $result = $nullCaseListAssignment->isValidForCaseWithSelectedOrganisation($case, $organisation2, $this->faker->boolean);

        $this->assertTrue($result);
    }

    public function testIsValidForCaseWithSelectedOrganisationButAssignedToOtherOrganisation(): void
    {
        $organisation1 = $this->createOrganisation();
        $organisation2 = $this->createOrganisation();
        $case = $this->createCase([
            'assigned_organisation_uuid' => $organisation1->uuid,
        ]);

        $nullCaseListAssignment = new NullCaseListAssignment();
        $result = $nullCaseListAssignment->isValidForCaseWithSelectedOrganisation($case, $organisation2, $this->faker->boolean);

        $this->assertFalse($result);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Permissions;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\FeatureTestCase;

use function array_merge;

class ArchiveCaseMultiPermissionTest extends FeatureTestCase
{
    #[TestDox('Archive multiple cases for scenario ownerPlanner is allowed')]
    public function testOwnerPlannerIsAllowedToArchiveMultipleCases(): void
    {
        $ownerPlanner = $this->createPlannerUser();
        $unOutsourcedCase = $this->createCaseForUser($ownerPlanner);

        /** @var Response $response */
        $response = $this->be($ownerPlanner)->putJson('api/cases/archiveMulti', [
            'cases' => [$unOutsourcedCase->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('Archive multiple cases for scenario outsourcedOrganisationPlanner is NOT allowed')]
    public function testArchiveMultipleCasesForOutsourcedPlanner(): void
    {
        $planner = $this->createPlannerUser();
        $outsourcedPlanner = $this->createPlannerUser();
        $case = $this->createCaseForOrganisation($planner->getRequiredOrganisation());

        /** @var Response $response */
        $response = $this->be($outsourcedPlanner)->putJson('api/cases/archiveMulti', [
            'cases' => [$case->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[TestDox('Archive multiple cases for scenario owningBcoUser is NOT allowed')]
    public function testArchiveMultipleCasesForOwningBcoUser(): void
    {
        $planner = $this->createPlannerUser();
        $owningBcoUser = $this->createUserForOrganisation($planner->getRequiredOrganisation());
        $case = $this->createCaseForOrganisation($planner->getRequiredOrganisation(), ['assigned_user_uuid' => $owningBcoUser->uuid]);

        /** @var Response $response */
        $response = $this->be($owningBcoUser)->putJson('api/cases/archiveMulti', [
            'cases' => [$case->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertEquals(403, $response->getStatusCode());
    }

    #[TestDox('Archive multiple outsourced cases by an planner of the outsourced organisation is allowed')]
    public function testOutsourcedOrganisationPlannerIsAllowedToArchiveMultipleCases(): void
    {
        $externalPlanner = $this->createPlannerUser();
        $outsourcedCase = $this->createOutsourcedCase(
            $this->createOrganisation(),
            $this->createUserForOrganisation($externalPlanner->getRequiredOrganisation()),
        );

        /** @var Response $response */
        $response = $this->be($externalPlanner)->putJson('api/cases/archiveMulti', [
            'cases' => [$outsourcedCase->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('Archive multiple cases for scenario OwnerPlanner is NOT allowed')]
    public function testArchiveOutsourcedMultipleCasesForOwnerPlannerIsNotAllowed(): void
    {
        $ownerPlanner = $this->createPlannerUser();
        $outsourcedCase = $this->createOutsourcedCase($ownerPlanner->getRequiredOrganisation(), $this->createUser());

        /** @var Response $response */
        $response = $this->be($ownerPlanner)->putJson('api/cases/archiveMulti', [
            'cases' => [$outsourcedCase->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[TestDox('Archive multiple cases for scenario otherPlanner is NOT allowed')]
    public function testArchiveOutsourcedMultipleCasesForOtherPlanner(): void
    {
        $otherPlanner = $this->createPlannerUser();

        $assignedOutsourceUser = $this->createUser();
        $outsourcedCase = $this->createOutsourcedCase($assignedOutsourceUser->getRequiredOrganisation(), $assignedOutsourceUser);

        /** @var Response $response */
        $response = $this->be($otherPlanner)->putJson('api/cases/archiveMulti', [
            'cases' => [$outsourcedCase->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[TestDox('Archive multiple cases for scenario owningBcoUser is NOT allowed')]
    public function testArchiveOutsourcedMultipleCasesForOwningBcoUser(): void
    {
        $assignedOutsourceUser = $this->createUser();
        $outsourcedCase = $this->createOutsourcedCase($assignedOutsourceUser->getRequiredOrganisation(), $assignedOutsourceUser);

        /** @var Response $response */
        $response = $this->be($assignedOutsourceUser)->putJson('api/cases/archiveMulti', [
            'cases' => [$outsourcedCase->uuid],
            'note' => $this->faker->sentence(),
        ]);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function archiveOutsourcedMultiActions(): array
    {
        return [
            ['otherPlanner'],
            ['owningBcoUser'],
        ];
    }

    private function createPlannerUser(): EloquentUser
    {
        return $this->createUser(
            [
                'consented_at' => CarbonImmutable::yesterday(),
            ],
            'planner',
        );
    }

    protected function createCaseForOrganisation(
        EloquentOrganisation $organisation,
        array $caseAttributes = [],
    ): EloquentCase {
        return parent::createCaseForOrganisation($organisation, array_merge([
            'created_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::completed(),
        ], $caseAttributes));
    }

    private function createOutsourcedCase(EloquentOrganisation $owningOrganisation, EloquentUser $outsourcedUser): EloquentCase
    {
        return $this->createCaseForOrganisation($owningOrganisation, [
            'assigned_organisation_uuid' => $outsourcedUser->getRequiredOrganisation()->uuid,
            'assigned_user_uuid' => $outsourcedUser->uuid,
        ]);
    }
}

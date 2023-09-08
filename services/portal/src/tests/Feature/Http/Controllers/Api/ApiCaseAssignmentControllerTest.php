<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Http\Response;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Repositories\AuditRepository;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function collect;
use function sprintf;

#[Group('case-assignment')]
class ApiCaseAssignmentControllerTest extends FeatureTestCase
{
    public function testSingleAssignment(): void
    {
        $user = $this->createUser([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => null,
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $user->uuid,
        ]);
        $response->assertStatus(200);
        $this->assertSame($case->uuid, $response->json()['uuid']);

        $case->refresh();
        $this->assertEquals($user->uuid, $case->assigned_user_uuid);
    }

    public function testSingleAssignmentToUnassigned(): void
    {
        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => $user->uuid,
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $response = $this->be($planner)->putJson(sprintf('/api/cases/%s/assignment', $case->uuid), [
            'assignedUserUuid' => null,
        ]);
        $response->assertStatus(200);

        $case->refresh();
        $this->assertNull($case->assigned_user_uuid);
    }

    public function testSingleAssignmentWithAssignedOrganisationUuidToUnassigned(): void
    {
        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => $user->uuid,
            'assigned_organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $response = $this->be($planner)->putJson(sprintf('/api/cases/%s/assignment', $case->uuid), [
            'assignedUserUuid' => null,
        ]);
        $response->assertStatus(200);

        $case->refresh();
        $this->assertNull($case->assigned_user_uuid);
    }

    public function testSingleAssignmentToCaseListOfTypeQueue(): void
    {
        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => $user->uuid,
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        // assert to case is assigned to the user, not the list
        $this->assertNull($case->assigned_case_list_uuid);
        $this->assertEquals($case->assigned_user_uuid, $user->uuid);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $caseList = $this->createCaseList([
            'organisation_uuid' => $organisation->uuid,
            'is_queue' => true,
        ]);

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedCaseListUuid' => $caseList->uuid,
        ]);
        $this->assertStatus($response, 200);
        $case->refresh();

        // assert to case is now assigned to the list, and no longer to the user
        $this->assertEquals($caseList->uuid, $case->assigned_case_list_uuid);
        $this->assertNull($case->assigned_user_uuid);
    }

    public function testSingleAssignmentToCaseListNotOfTypeQueue(): void
    {
        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => $user->uuid,
            'organisation_uuid' => $organisation->uuid,
        ]);

        // assert to case is assigned to the user, not the list
        $this->assertNull($case->assigned_case_list_uuid);
        $this->assertEquals($case->assigned_user_uuid, $user->uuid);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $caseList = $this->createCaseList([
            'organisation_uuid' => $organisation->uuid,
            'is_queue' => false,
        ]);

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedCaseListUuid' => $caseList->uuid,
        ]);
        $this->assertStatus($response, 200);
        $case->refresh();

        // assert to case is now assigned to the list, and no longer to the user
        $this->assertEquals($caseList->uuid, $case->assigned_case_list_uuid);
        $this->assertNotNull($case->assigned_user_uuid);
    }

    public function testSingleAssignmentToOutsourceOrganisation(): void
    {
        $outsourceOrganisation = $this->createOrganisation([
            'isAvailableForOutsourcing' => true,
        ]);

        $organisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $outsourceOrganisation->uuid,
        ]);
        $this->assertStatus($response, 200);
        $case->refresh();
        $this->assertEquals($outsourceOrganisation->uuid, $case->assigned_organisation_uuid);
    }

    public function testSingleAssignmentValidation(): void
    {
        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation = $this->createOrganisation();
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $plannerOutsourceOrganisation = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');

        $caseList = $this->createCaseList();
        $user = $this->createUser();

        // invalid case id
        $response = $this->be($planner)->putJson('/api/cases/nonexisting/assignment', [
            'assignedUserUuid' => $user->uuid,
        ]);
        $this->assertStatus($response, 404);

        // invalid user id
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => 'nonexisting',
        ]);
        $this->assertStatus($response, 422);

        // invalid user role
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $planner->uuid,
        ]);
        $this->assertStatus($response, 422);

        // user not part of this organisation
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $plannerOutsourceOrganisation->uuid,
        ]);
        $this->assertStatus($response, 422);

        // invalid case list id
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedCaseListUuid' => 'nonexisting',
        ]);
        $this->assertStatus($response, 422);

        // null case list id
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedCaseListUuid' => null,
        ]);
        $this->assertStatus($response, 200);

        // not our own case list
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedCaseListUuid' => $caseList->uuid,
        ]);
        $this->assertStatus($response, 422);

        // invalid organisation id
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => Uuid::uuid4(),
        ]);
        $this->assertStatus($response, 422);

        // can't outsource to ourselves
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $organisation->uuid,
        ]);
        $this->assertStatus($response, 422);

        // only 1 assignment change at a time
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $user->uuid,
            'assignedOrganisationUuid' => $outsourceOrganisation->uuid,
        ]);
        $this->assertStatus($response, 422);
    }

    public function testReturnToOwnerAfterAssignmentToOrganisation(): void
    {
        // Given an Organisation that can outsource to another Organisation
        $organisation = $this->createOrganisation();
        $outsourceOrganisation = $this->createOrganisation([
            'isAvailableForOutsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        // And an open case owned by this Organisation
        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        // And a User with the role "planner"
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // And this User being logged in
        $this->be($planner);

        // When the Case is assigned to the other Organisation
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $outsourceOrganisation->uuid,
        ]);
        $this->assertStatus($response, Response::HTTP_OK);

        // And the Case is returned to the owner Organisation
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => null,
        ]);
        $this->assertStatus($response, Response::HTTP_OK);

        // Then the Case should not be assigned to an Organisation
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => null,
        ]);
    }

    public function testReturnToOwnerAfterAssignmentToOrganisationAndUserNotAllowed(): void
    {
        // Given an Organisation that can outsource to another Organisation
        $organisation = $this->createOrganisation();
        $outsourceOrganisation = $this->createOrganisation([
            'isAvailableForOutsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        // And an open case owned by this Organisation
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);

        // And a User with the role "planner" belonging to this Organisation
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // And this User being logged in
        $this->be($planner);

        // And another User with the role "planner" belonging to the other Organisation
        $plannerOutsourceOrganisation = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');

        // And another User with the role "user" belong to the other Organisation
        $userOutsourceOrganisation = $this->createUserForOrganisation($outsourceOrganisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ], 'user');

        // When the Case is assigned to the other Organisation
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $outsourceOrganisation->uuid,
        ]);
        $this->assertStatus($response, Response::HTTP_OK);

        // And the "planner" from the other Organisation assigns the Case to the "user"
        $this->be($plannerOutsourceOrganisation);
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $userOutsourceOrganisation->uuid,
        ]);
        $this->assertStatus($response, Response::HTTP_OK);

        // And the User from the owner Organisation takes the Case back from the other Organisation
        $this->be($planner);
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => null,
        ]);

        // Then returning the Case to the owner Organisation should should have failed
        $this->assertStatus($response, Response::HTTP_UNPROCESSABLE_ENTITY);

        // And the Case should still be assigned to the other User from the other Organisation
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
            'assigned_user_uuid' => $userOutsourceOrganisation->uuid,
        ]);
    }

    public function testAssignToYetAnotherOrganisationAfterAssignmentToOrganisationNotAllowed(): void
    {
        // Given an Organisation that can outsource to a second Organisation
        $organisation1 = $this->createOrganisation();
        $organisation2 = $this->createOrganisation([
            'isAvailableForOutsourcing' => true,
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $organisation1->outsourceOrganisations()->attach($organisation2);

        // And an open case owned by this Organisation
        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'organisation_uuid' => $organisation1->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        // And a third Organisation that is available for outsourcing
        $organisation3 = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);

        // And a User with the role "planner" belonging to the first Organisation
        $planner1 = $this->createUserForOrganisation($organisation1, [], 'planner');

        // And this User being logged in
        $this->be($planner1);

        // And another User with the role "planner" belonging to the second Organisation
        $planner2 = $this->createUserForOrganisation($organisation2, [], 'planner');

        // When the Case is assigned to the second Organisation
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $organisation2->uuid,
        ]);
        $this->assertStatus($response, Response::HTTP_OK);

        // And the "planner" from the second Organisation logs in
        $this->be($planner2);

        // And the Case is assigned to the third Organisation
        $response = $this->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $organisation3->uuid,
        ]);

        // Then the last Organisation assignment should have failed
        $this->assertStatus($response, Response::HTTP_UNPROCESSABLE_ENTITY);

        // And the Case should still be assigned to the second Organisation
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => $organisation2->uuid,
        ]);
    }

    public function testSingleAssignmentAddedToAuditLog(): void
    {
        $auditRepository = $this->spy(AuditRepository::class);

        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => $user->uuid,
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::draft(),
        ]);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => null,
        ]);
        $response->assertStatus(200);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($case) {
                /** @var AuditObject $object */
                $object = collect($event->getObjects())->first(static fn (AuditObject $object) => $object->getType() === 'case');
                if ($object === null) {
                    return false;
                }
                return $object->getDetails()['properties']['bcoStatus'] === $case->bcoStatus->value;
            }));
    }

    public function testGetAssignmentOptionsMulti(): void
    {
        $planner = $this->createUser([], 'planner');

        $case1 = $this->createCaseForUser($planner);
        $case2 = $this->createCaseForUser($planner);

        $response = $this->be($planner)
            ->getJson(sprintf('api/cases/assignment/options?cases[]=%s&%s', $case1->uuid, $case2->uuid));

        $response->assertStatus(200);
    }

    public function testMultiAssignment(): void
    {
        $user = $this->createUser([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $organisation = $user->organisations->first();

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        /** @var EloquentCase $case1 */
        $case1 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);
        /** @var EloquentCase $case2 */
        $case2 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->putJson('/api/cases/assignment', [
            'assignedUserUuid' => $user->uuid,
            'cases' => [
                $case1->uuid,
                $case2->uuid,
            ],
        ]);
        $response->assertStatus(204);
    }

    public function testMultiAssignmentValidation(): void
    {
        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        /** @var EloquentCase $case1 */
        $case1 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
        ]);
        /** @var EloquentCase $case2 */
        $case2 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
        ]);

        // invalid user id
        $response = $this->be($planner)->putJson('/api/cases/assignment/', [
            'assignedUserUuid' => 'nonexisting',
            'cases' => [$case1->uuid, $case2->uuid],
        ]);

        $this->assertStatus($response, 422);
    }

    public function testMultiAssignmentValidationNonExistingCase(): void
    {
        $user = $this->createUser();
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        /** @var EloquentCase $case1 */
        $case1 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
        ]);
        /** @var EloquentCase $case2 */
        $case2 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
        ]);

        // invalid user id
        $response = $this->be($planner)->putJson('/api/cases/assignment/', [
            'assignedUserUuid' => 'nonexisting',
            'cases' => [$case1->uuid, $case2->uuid, 'foo'],
        ]);

        $this->assertStatus($response, 422);
    }

    public function testMultiAssignmentAddedToAuditLog(): void
    {
        $auditRepository = $this->spy(AuditRepository::class);

        $user = $this->createUser([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $organisation = $user->organisations->first();

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        /** @var EloquentCase $case1 */
        $case1 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::draft(),
        ]);
        /** @var EloquentCase $case2 */
        $case2 = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::archived(),
        ]);

        $response = $this->be($planner)->putJson('/api/cases/assignment', [
            'assignedUserUuid' => $user->uuid,
            'cases' => [
                $case1->uuid,
                $case2->uuid,
            ],
        ]);
        $response->assertStatus(204);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($case1, $case2) {
                $statuses = collect($event->getObjects())->mapWithKeys(
                    static fn(AuditObject $object) => [$object->getIdentifier() => $object->getDetails()['properties']['bcoStatus']]
                );

                $expected = [$case1->uuid => $case1->bcoStatus->value, $case2->uuid => $case2->bcoStatus->value];

                return $expected === $statuses->all();
            }));
    }

    public function testNextCaseFetchesInRightOrder(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $caseList = $this->createCaseList([
            'is_default' => 1,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        /** @var EloquentCase $caseExpectedFirst */
        $caseExpectedFirst = EloquentCase::factory()->create([
            'created_at' => new DateTime('2021-09-01 10:00:00'),
            'date_of_test' => null,
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => null,
            'organisation_uuid' => $organisation->uuid,
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        /** @var EloquentCase $caseExpectedSecond */
        $caseExpectedSecond = EloquentCase::factory()->create([
            'created_at' => new DateTime('2021-09-02 10:00:00'),
            'date_of_test' => null,
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => null,
            'organisation_uuid' => $organisation->uuid,
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        /** @var EloquentCase $caseExpectedThird */
        $caseExpectedThird = EloquentCase::factory()->create([
            'created_at' => new DateTime('2021-09-01 07:00:00'),
            'date_of_test' => new DateTime('2021-07-02 00:00:00'),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => null,
            'organisation_uuid' => $organisation->uuid,
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        /** @var EloquentCase $caseExpectedFourth */
        $caseExpectedFourth = EloquentCase::factory()->create([
            'created_at' => new DateTime('2021-09-01 05:00:00'),
            'date_of_test' => new DateTime('2021-09-02 00:00:00'),
            'updated_at' => CarbonImmutable::now(),
            'assigned_user_uuid' => null,
            'organisation_uuid' => $organisation->uuid,
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        $validCaseUuids = collect([
            $caseExpectedFirst->uuid,
            $caseExpectedSecond->uuid,
            $caseExpectedThird->uuid,
            $caseExpectedFourth->uuid,
        ]);

        foreach ($validCaseUuids as $expectedCaseUuid) {
            $response = $this->be($user)->getJson('/api/casequeues/default/next');
            $response->assertStatus(200);

            $caseUuidFromResponse = $response->json('caseUuid');
            $this->assertEquals($expectedCaseUuid, $caseUuidFromResponse);
        }

        $response = $this->getJson('/api/casequeues/default/next');
        $this->assertStatus($response, 404);
    }

    public function testAssignmentOptionsNonAssigned(): void
    {
        $planner = $this->createUser([], 'planner');
        /** @var EloquentOrganisation $organisation */
        $organisation = $planner->organisations->first();

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $caseList = $this->createCaseList([
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $otherUser1 = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser1->organisations()->attach($organisation->uuid);

        $otherUser2 = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser2->organisations()->attach($organisation->uuid);

        $this->createCaseList([
            'name' => 'Lijst 1',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseList([
            'name' => 'Lijst 2',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseList([
            'name' => 'Lijst 3',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::open(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);

        $this->assertOption($options, 'Niet toegewezen', null, function (array $option): void {
            $this->assertEquals(0, $option['index']);
            $this->assertTrue($option['isSelected']);
            $this->assertFalse($option['isEnabled']);
        });

        $this->assertOption($options, 'Wachtrij', null, function (array $option) use ($caseList): void {
            $this->assertEquals(1, $option['index']);
            $this->assertFalse($option['isSelected']);
            $this->assertEquals($caseList->uuid, $option['assignment']['assignedCaseListUuid']);
            $this->assertEquals('caseList', $option['assignmentType']);
        });

        $this->assertOption($options, 'Lijsten', null, function (array $option): void {
            $this->assertEquals(2, $option['index']);
            $this->assertFalse(isset($option['isSelected']));
            $this->assertCount(4, $option['options']);
            foreach ($option['options'] as $childOption) {
                $this->assertEquals('caseList', $childOption['assignmentType']);
            }
        });

        $this->assertOption($options, 'Uitbesteden', null, function (array $option): void {
            $this->assertEquals(3, $option['index']);
            $this->assertTrue(!isset($option['isSelected']));
            $this->assertCount(1, $option['options']);
            foreach ($option['options'] as $childOption) {
                $this->assertEquals('organisation', $childOption['assignmentType']);
            }
        });

        $this->assertCount(
            2,
            array_filter(
                $options,
                static fn ($option) => ($option['assignmentType'] ?? null === 'user') && !empty($option['assignment']['assignedUserUuid'])
            ),
        );
    }

    public function testAssignmentOptionsAssignedToOrganisation(): void
    {
        $planner = $this->createUser([], 'planner');
        /** @var EloquentOrganisation $organisation */
        $organisation = $planner->organisations->first();

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertNull($options[0]['assignment']['assignedOrganisationUuid']);
    }

    public function testAssignmentOptionsAssignedToDefaultQueue(): void
    {
        $planner = $this->createUser([], 'planner');
        /** @var EloquentOrganisation $organisation */
        $organisation = $planner->organisations->first();

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $caseList = $this->createCaseList([
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $otherUser1 = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser1->organisations()->attach($organisation->uuid);

        $otherUser2 = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser2->organisations()->attach($organisation->uuid);

        $this->createCaseList([
            'name' => 'Lijst 1',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseList([
            'name' => 'Lijst 2',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseList([
            'name' => 'Lijst 3',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::open(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);

        $this->assertOption($options, 'Niet toegewezen', null, function (array $option): void {
            $this->assertEquals(0, $option['index']);
            $this->assertFalse($option['isSelected']);
        });

        $this->assertOption($options, 'Wachtrij', null, function (array $option): void {
            $this->assertEquals(1, $option['index']);
            $this->assertTrue($option['isSelected']);
            $this->assertEquals(null, $option['assignment']['assignedCaseListUuid']);
        });

        $this->assertOption($options, 'Lijsten', null, function (array $option): void {
            $this->assertEquals(2, $option['index']);
            $this->assertTrue(!isset($option['isSelected']));
            $this->assertCount(4, $option['options']);
        });

        $this->assertOption($options, 'Uitbesteden', null, function (array $option): void {
            $this->assertEquals(3, $option['index']);
            $this->assertTrue(!isset($option['isSelected']));
            $this->assertCount(1, $option['options']);
        });

        $this->assertCount(2, array_filter($options, static fn ($o) => !empty($o['assignment']['assignedUserUuid'])));
    }

    public function testAssignmentOptionsAssignedToCaseList(): void
    {
        $planner = $this->createUser([], 'planner');
        /** @var EloquentOrganisation $organisation */
        $organisation = $planner->organisations->first();

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $caseList = $this->createCaseList([
            'name' => 'Wachtrij',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $otherUser1 = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser1->organisations()->attach($organisation->uuid);

        $otherUser2 = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser2->organisations()->attach($organisation->uuid);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::open(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);

        $this->assertOption($options, 'Niet toegewezen', null, function (array $option): void {
            $this->assertEquals(0, $option['index']);
            $this->assertTrue($option['isSelected']);
        });

        $this->assertOption($options, 'Lijsten', null, function (array $option): void {
            $this->assertEquals(1, $option['index']);
            $this->assertCount(2, $option['options']);
            $this->assertFalse($option['options'][0]['isSelected']);
            $this->assertTrue($option['options'][1]['isSelected']);
        });

        $this->assertOption($options, 'Uitbesteden', null, function (array $option): void {
            $this->assertEquals(2, $option['index']);
            $this->assertTrue(!isset($option['isSelected']));
            $this->assertCount(1, $option['options']);
        });

        $this->assertCount(2, array_filter($options, static fn ($o) => !empty($o['assignment']['assignedUserUuid'])));
    }

    public function testAssignmentOptionsAssignedToUser(): void
    {
        $user = $this->createUser([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $organisation = $user->organisations->first();

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        $caseList = $this->createCaseList([
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $this->createCaseList([
            'name' => 'Lijst 1',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseList([
            'name' => 'Lijst 2',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseList([
            'name' => 'Lijst 3',
            'is_default' => 0,
            'is_queue' => 1,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $otherUser = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $otherUser->organisations()->attach($organisation->uuid);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_user_uuid' => $user->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);

        $this->assertOption($options, 'Niet toegewezen', null, function (array $option): void {
            $this->assertEquals(0, $option['index']);
            $this->assertFalse($option['isSelected']);
        });

        $this->assertOption($options, 'Wachtrij', null, function (array $option) use ($caseList): void {
            $this->assertEquals(1, $option['index']);
            $this->assertFalse($option['isSelected']);
            $this->assertEquals($caseList->uuid, $option['assignment']['assignedCaseListUuid']);
        });

        $this->assertOption($options, 'Lijsten', null, function (array $option): void {
            $this->assertEquals(2, $option['index']);
            $this->assertTrue(!isset($option['isSelected']));
            $this->assertCount(4, $option['options']);
        });

        $this->assertOption($options, 'Uitbesteden', null, function (array $option): void {
            $this->assertEquals(3, $option['index']);
            $this->assertTrue(!isset($option['isSelected']));
            $this->assertCount(1, $option['options']);
        });

        $this->assertCount(1, array_filter($options, static fn ($o) => !empty($o['assignment']['assignedUserUuid'])));
        $this->assertCount(
            1,
            array_filter($options, static function ($o) {
                return
                    isset($o['assignment']) &&
                    ($o['isSelected'] ?? false) &&
                    array_key_exists('assignedUserUuid', $o['assignment']) &&
                    $o['assignment']['assignedUserUuid'] === null;
            }),
        );
    }

    public function testAssignmentOptionsForCaseWhenUserInOrganisationWithoutRoles(): void
    {
        $user = $this->createUser();
        $organisation = $user->organisations->first();

        $this->createUserForOrganisation($organisation, [], null);

        $planner = $this->createUserWithoutOrganisation([], 'planner');
        $planner->organisations()->attach($organisation);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($planner)->getJson(
            sprintf('/api/cases/%s/assignment/options?cases[]=%s', $case->uuid, $case->uuid),
        );
        $this->assertStatus($response, 200);
    }

    public function testAssignmentOptionsForCompletedCaseAndCasequalityRole(): void
    {
        $planner = $this->createUser([], 'planner');
        /** @var EloquentOrganisation $organisation */
        $organisation = $planner->organisations->first();

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $userRegular = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $userRegular->organisations()->attach($organisation->uuid);

        $userRegularAndCasequality = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ], 'user,casequality');
        $userRegularAndCasequality->organisations()->attach($organisation->uuid);

        $userCasequality = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ], 'casequality');
        $userCasequality->organisations()->attach($organisation->uuid);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::completed(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);

        $expectedUuids = [
            $userCasequality->uuid,
            $userRegularAndCasequality->uuid,
        ];

        $actualUuids = array_map(
            static fn ($option) => $option['assignment']['assignedUserUuid'],
            array_filter($options, static fn ($option) => ($option['assignmentType'] ?? null === 'user')),
        );

        $this->assertEqualsCanonicalizing(
            $expectedUuids,
            $actualUuids,
            'Only the two users with role casequality can be assigned to completed case.',
        );
    }

    public function testAssignmentOptionsForOpenCaseAndCasequalityRole(): void
    {
        $planner = $this->createUser([], 'planner');
        /** @var EloquentOrganisation $organisation */
        $organisation = $planner->organisations->first();

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => true,
        ]);
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $userRegular = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ]);
        $userRegular->organisations()->attach($organisation->uuid);

        $userRegularAndCasequality = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ], 'user,casequality');
        $userRegularAndCasequality->organisations()->attach($organisation->uuid);

        $userCasequality = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(),
        ], 'casequality');
        $userCasequality->organisations()->attach($organisation->uuid);

        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::open(),
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($planner)->getJson('/api/cases/' . $case->uuid . '/assignment/options');
        $this->assertStatus($response, 200);

        $options = $response->json('options');
        $this->assertIsArray($options);

        $expectedUuids = [
            $userRegular->uuid,
            $userRegularAndCasequality->uuid,
        ];

        $actualUuids = array_map(
            static fn ($option) => $option['assignment']['assignedUserUuid'],
            array_filter($options, static fn ($option) => ($option['assignmentType'] ?? null === 'user')),
        );

        $this->assertEqualsCanonicalizing($expectedUuids, $actualUuids, 'Only the two users with role user can be assigned to open case.');
    }

    /**
     * Recursively find option with the given label.
     *
     * @param array $options
     *
     * @return array|null
     */
    private function getOption(array $options, string $label, ?string $menuLabel = null): ?array
    {
        foreach ($options as $i => $option) {
            if ($option['type'] === 'separator') {
                continue;
            }

            if ($menuLabel !== null && $option['type'] === 'menu' && $option['label'] === $menuLabel) {
                return $this->getOption($option['options'], $label);
            }

            if ($menuLabel === null && $option['label'] === $label) {
                return array_merge($option, ['index' => $i]);
            }
        }

        return null;
    }

    private function assertOptionExists(array $options, string $label, ?string $menuLabel = null): void
    {
        $this->assertNotNull($this->getOption($options, $label, $menuLabel));
    }

    private function assertNotOptionExists(array $options, string $label, ?string $menuLabel = null): void
    {
        $this->assertNull($this->getOption($options, $label, $menuLabel));
    }

    private function assertOption(array $options, string $label, ?string $menuLabel, callable $assertions): void
    {
        $option = $this->getOption($options, $label, $menuLabel);
        $this->assertNotNull($option);
        $assertions($option);
    }

    public function testGetUserAssignmentOptions(): void
    {
        // GIVEN a user exists
        $user = $this->createUser(['last_login_at' => CarbonImmutable::now()]);
        /** @var EloquentOrganisation $organisation */
        $organisation = $user->organisations->first();
        // AND a planner user exists belonging to that same organisation
        $planner = $this->createUserWithoutOrganisation(['last_login_at' => CarbonImmutable::now()], 'planner');
        $planner->organisations()->attach($organisation);

        // WHEN the planner gets all user options
        $response = $this->be($planner)->get('/api/cases/assignment/all-user-options');

        // THEN the response is ok
        $response->assertStatus(200);
        // AND the response is a JSON object with options as an array
        $options = $response->json('options');
        $this->assertIsArray($options);
        // AND the array contains 2 users
        $this->assertCount(2, array_filter($options, static fn ($o) => !empty($o['assignment']['assignedUserUuid'])));
        // AND the planner is one of them
        $this->assertCount(
            1,
            array_filter($options, static fn ($o) => !empty($o['assignment']) && $o['assignment']['assignedUserUuid'] === $planner->uuid),
        );
        // AND the user is the other
        $this->assertCount(
            1,
            array_filter($options, static fn ($o) => !empty($o['assignment']) && $o['assignment']['assignedUserUuid'] === $user->uuid),
        );
    }

    public function testNextCaseForDefaultQueueResponseCodeWhenUserHasNoOrganisation(): void
    {
        $user = $this->createUserWithoutOrganisation();

        $response = $this->be($user)->getJson('/api/casequeues/default/next');
        $this->assertStatus($response, 404);
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: tests for outsource organisation
}

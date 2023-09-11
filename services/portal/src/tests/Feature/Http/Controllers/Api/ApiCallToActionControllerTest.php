<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Dto\Chore\Resource;
use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\TokenResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CallToActionEvent;
use MinVWS\DBCO\Enum\Models\Permission;
use MinVWS\DBCO\Enum\Models\ResourcePermission;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function collect;
use function count;
use function explode;
use function in_array;

#[Group('call-to-action')]
final class ApiCallToActionControllerTest extends FeatureTestCase
{
    private const BASE_PATH = '/api/call-to-actions';

    private AssignmentTokenService $assignmentTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assignmentTokenService = $this->app->make(AssignmentTokenService::class);
    }

    public function testListCallToActionsWhenNotLoggedInIsForbidden(): void
    {
        $this->getJson(self::BASE_PATH)->assertUnauthorized();
    }

    public function testListCallToActionsWhenLoggedInWithoutPermissionIsForbidden(): void
    {
        $this->be($this->createUser([], ''));
        $this->getJson(self::BASE_PATH)->assertForbidden();
    }

    public function testListCallToActionsWhenNoneExistReturnsEmptyPaginatedResponse(): void
    {
        $this->be($this->createUser());
        $response = $this->get(self::BASE_PATH);
        $response->assertOk();
        $response->assertJson([
            'from' => null,
            'to' => null,
            'total' => 0,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [],
        ]);
    }

    public function testListCallToActionsWhenCallToActionOwnedByOrganisationExists(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $dateTime = CarbonImmutable::now()->floorSeconds();
        $expiresAt = CarbonImmutable::parse($dateTime)->addMonth();
        $permission = ResourcePermission::edit();
        $callToActionAttributes = ['subject' => $this->faker->text(50), 'created_by' => $user->uuid];
        $callToAction = $this->createCallToAction($callToActionAttributes);
        $resource = $this->createResourceForCase();
        $ownerResource = $this->createResourceForCallToAction($callToAction);

        $this->createChore([
            'organisation_uuid' => $organisation->uuid,
            'resource_type' => $resource->type,
            'resource_id' => $resource->id,
            'resource_permission' => $permission,
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
            'expires_at' => $expiresAt,
        ]);

        $this->be($user);
        $response = $this->get(self::BASE_PATH);
        $response->assertOk();
        $response->assertJson([
            'from' => 1,
            'to' => 1,
            'total' => 1,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [
                [
                    'uuid' => $callToAction->uuid,
                    'subject' => $callToAction->subject,
                    'description' => $callToAction->description,
                    'organisationUuid' => $organisation->uuid,
                    'resource' => [
                        'uuid' => $resource->id,
                        'type' => $resource->type,
                    ],
                    'createdBy' => [
                        'name' => $user->name,
                        'roles' => explode(',', $user->roles),
                        'uuid' => $user->uuid,
                    ],
                    'createdAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
                    'expiresAt' => $expiresAt->format('Y-m-d\TH:i:s\Z'),
                    'assignedUserUuid' => null,
                ],
            ],
        ]);
    }

    public function testListCallToActionsWhenCallToActionOwnedByAnotherOrganisationExists(): void
    {
        $dateTime = CarbonImmutable::now()->floorSeconds();
        $expiresAt = CarbonImmutable::parse($dateTime)->addMonth();
        $permission = ResourcePermission::edit();
        $callToActionAttributes = ['subject' => $this->faker->text(50)];
        $otherOrganisation = $this->createOrganisation();
        $callToAction = $this->createCallToAction($callToActionAttributes);
        $organisation = $this->createOrganisation();
        $resource = $this->createResourceForCase();
        $ownerResource = $this->createResourceForCallToAction($callToAction);
        $this->createChore([
            'organisation_uuid' => $otherOrganisation->uuid,
            'resource_type' => $resource->type,
            'resource_id' => $resource->id,
            'resource_permission' => $permission,
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
            'expires_at' => $expiresAt,
        ]);

        $this->be($this->createUserForOrganisation($organisation));
        $response = $this->get(self::BASE_PATH);

        $response->assertOk();
        $response->assertJson([
            'from' => null,
            'to' => null,
            'total' => 0,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [],
        ]);
    }

    public function testListCallToActionsDefaultSorting(): void
    {
        $dateTime = CarbonImmutable::now()->floorSeconds();
        $permission = Permission::choreList();
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        // Create 4 CallToActions with different expiry dates and assignments
        for ($i = 4; $i > 0; $i--) {
            $callToActionAttributes = ['subject' => $i];
            $callToAction = $this->createCallToAction($callToActionAttributes);
            $resource = $this->createResourceForCase();
            $ownerResource = $this->createResourceForCallToAction($callToAction);

            $chore = $this->createChore([
                'organisation_uuid' => $organisation->uuid,
                'resource_type' => $resource->type,
                'resource_id' => $resource->id,
                'resource_permission' => $permission,
                'owner_resource_type' => $ownerResource->type,
                'owner_resource_id' => $ownerResource->id,
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
                'expires_at' => CarbonImmutable::parse($dateTime)->addDays($i + 4),
            ]);

            if ($i % 2) {
                $this->assignChoreToUser($chore, $user);
            }
        }

        $this->be($user);
        $response = $this->get(self::BASE_PATH);
        $response->assertOk();

        // CallToActions with uneven subject numbers are assigned and listed first
        // Within each (un)assigned group, CallToActions with the soonest expiry (low number) are displayed first
        $this->assertEquals([1, 3, 2, 4], collect($response->json('data'))->pluck('subject')->toArray());
    }

    /**
     * This test creates a number of CallToActions where some are connected to a not covid-case resource. The list
     * result should only return CallToActions which are connected to a CovidCase. Furthermore the first CallToAction
     * is retrieved to test if a heavily joined query with a lot of where clauses returns the expected
     * record from a database with multiple records.
     */
    public function testListCallToActionsWithMultipleResourceTypesAndRetrieveFirst(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $this->be($user);

        $firstCase = $this->createCaseForOrganisation($organisation);
        $firstCallToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($firstCallToAction);
        $this->createChoreForCaseAndOrganisation($firstCase, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        $otherResourceCase = $this->createCaseForOrganisation($organisation);
        $otherResourceCallToAction = $this->createCallToAction([
            'subject' => 'Other Resource',
        ]);
        $resource = $this->createResourceForCallToAction($otherResourceCallToAction);
        $resource = new Resource('other-type', $otherResourceCallToAction->uuid);
        $this->createChoreForCaseAndOrganisation($otherResourceCase, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        $case = $this->createCaseForOrganisation($organisation);

        // Create 4 CallToActions
        for ($i = 4; $i > 0; $i--) {
            $callToAction = $this->createCallToAction();
            $resource = $this->createResourceForCallToAction($callToAction);
            $this->createChoreForCaseAndOrganisation($case, $organisation, [
                'owner_resource_type' => $resource->type,
                'owner_resource_id' => $resource->id,
            ]);
        }

        $this->be($user);
        $responseList = $this->get(self::BASE_PATH);
        $responseList->assertOk();

        $this->assertEquals(5, count($responseList->json('data')));

        $uuidsInResponse = collect($responseList->json('data'))->pluck('uuid')->toArray();

        $responseGet = $this->get(self::BASE_PATH . '/' . $firstCallToAction->uuid);
        $responseGet->assertOk();

        $this->assertEquals(5, count($uuidsInResponse));
        $this->assertTrue(in_array($firstCallToAction->uuid, $uuidsInResponse, true));
        $this->assertFalse(in_array($otherResourceCallToAction->uuid, $uuidsInResponse, true));
    }

    public function testListCallToActionWillBeShownWhenAssignedButExpired(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $dateTime = CarbonImmutable::now()->floorSeconds();
        $expiresAt = CarbonImmutable::parse($dateTime)->addMonth();
        $permission = ResourcePermission::edit();
        $callToActionAttributes = ['subject' => $this->faker->text(50), 'created_by' => $user->uuid];
        $callToAction = $this->createCallToAction($callToActionAttributes);
        $resource = $this->createResourceForCase();
        $ownerResource = $this->createResourceForCallToAction($callToAction);

        $chore = $this->createChore([
            'organisation_uuid' => $organisation->uuid,
            'resource_type' => $resource->type,
            'resource_id' => $resource->id,
            'resource_permission' => $permission,
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
            'expires_at' => $expiresAt,
        ]);

        $this->createAssignmentForChore($chore, [
            'user_uuid' => $user->uuid,
            'expires_at' => $this->faker->dateTime(CarbonImmutable::now()->subDay()),
        ]);

        $this->be($user);
        $response = $this->get(self::BASE_PATH);
        $response->assertOk();
        $response->assertJson([
            'from' => 1,
            'to' => 1,
            'total' => 1,
            'currentPage' => 1,
            'lastPage' => 1,
            'data' => [
                [
                    'uuid' => $callToAction->uuid,
                    'subject' => $callToAction->subject,
                    'description' => $callToAction->description,
                    'organisationUuid' => $organisation->uuid,
                    'resource' => [
                        'uuid' => $resource->id,
                        'type' => $resource->type,
                    ],
                    'createdBy' => [
                        'name' => $user->name,
                        'roles' => explode(',', $user->roles),
                        'uuid' => $user->uuid,
                    ],
                    'createdAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
                    'expiresAt' => $expiresAt->format('Y-m-d\TH:i:s\Z'),
                    'assignedUserUuid' => null,
                ],
            ],
        ]);
    }

    public function testGetCallToActionWhenCallToActionDoesNotExist(): void
    {
        // GIVEN user is logged in
        $this->be($this->createUser());

        // WHEN user gets a Call To Action which does not exist
        $response = $this->get(self::BASE_PATH . '/' . $this->faker->uuid);

        // THEN a NOT FOUND response is returned
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetCallToActionHistoryWhenNotLoggedInIsForbidden(): void
    {
        $this->getJson(self::BASE_PATH . '/' . $this->faker->uuid())->assertUnauthorized();
    }

    public function testGetCallToActionWhenCaseIsSoftDeleted(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $organisation = $user->getOrganisation();

        // Create a case with a CallToAction
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $chore = $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        // Assign the logged in user to the Chore associated with the CallToAction
        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $case->delete();

        // WHEN user gets a Call To Action which does not exist
        $response = $this->get(self::BASE_PATH . '/' . $resource->id);

        // THEN a NOT FOUND response is returned
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetCallToActionWhenCallToActionOwnedByOrganisationExists(): void
    {
        // GIVEN user from organisation is logged in
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));

        // AND a Call to Action from organisation exists which is not assigned
        $callToActionResource = $this->createResourceForCallToAction();
        $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
        ]);

        // WHEN user gets that Call To Action
        $response = $this->get(self::BASE_PATH . '/' . $callToActionResource->id);

        // THEN a OK response is returned which has an ID and a ID of that organisation
        $response->assertOk();
        $response->assertJson(['uuid' => $callToActionResource->id, 'organisationUuid' => $organisation->uuid], false);
    }

    public function testGetCallToActionWhenCallToActionOwnedByAnotherOrganisationDoesNotExist(): void
    {
        // GIVEN user is logged in
        $this->be($this->createUser());

        // AND a Call to Action from a different organisation exists
        $organisation = $this->createOrganisation();
        $callToActionResource = $this->createResourceForCallToAction();
        $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
        ]);

        // WHEN user gets that Call To Action
        $response = $this->get(self::BASE_PATH . '/' . $callToActionResource->id);

        // THEN a NOT FOUND response is returned
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetCallToActionWhenCallToActionAssignedToOtherUserGone(): void
    {
        // GIVEN user from organisation is logged in
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));

        // AND a Call to Action exists
        $callToActionResource = $this->createResourceForCallToAction();
        $chore = $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
        ]);

        // AND that Call to Action is assigned to another user
        $anotherUser = $this->createUserForOrganisation($organisation);
        $this->createAssignmentWithUserForChore($anotherUser, $chore);

        // WHEN user gets that Call To Action
        $response = $this->get(self::BASE_PATH . '/' . $callToActionResource->id);

        // THEN a GONE response is returned
        $response->assertStatus(Response::HTTP_GONE);
    }

    public function testCanCreateCallToActionWithChore(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();
        $case = $this->createCaseForUser($user);

        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $subject = $this->faker->word(),
            'description' => $description = $this->faker->sentence(),
            'organisation_uuid' => $organisation->uuid,
            'resource_uuid' => $case->uuid,
            'resource_type' => 'covid-case',
            'resource_permission' => ResourcePermission::edit(),
            'expires_at' => null,
        ]);

        $response->assertOk();
        $response->assertJson([
            'subject' => $subject,
            'description' => $description,
            'organisationUuid' => $organisation->uuid,
        ]);
    }

    public function testCannotCreateCallToActionWithBadRequest(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $case = $this->createCaseForUser($user);

        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $this->faker->words,
            'organisation' => $this->faker->uuid(),
            'resource_uuid' => $case->uuid,
            'resource_type' => 'covid-case',
        ]);

        $response->assertJsonValidationErrors([
            'subject',
            'description',
            'resource_permission',
        ]);
    }

    public function testCanCreateCallToActionInvalidResourceType(): void
    {
        $this->be($this->createUser());
        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $this->faker->words,
            'organisation' => $this->faker->uuid(),
            'resource_type' => $this->faker->word(),
        ]);

        $response->assertForbidden();
    }

    public function testCanCreateCallToActionInvalidResourceUuid(): void
    {
        $this->be($this->createUser());
        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $this->faker->words,
            'organisation' => $this->faker->uuid(),
            'resource_uuid' => $this->faker->uuid(),
            'resource_type' => 'covid-case',
        ]);

        $response->assertForbidden();
    }

    public function testCanCreateCallToActionNoOrganisation(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();
        $case = $this->createCaseForUser($user);

        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'resource_uuid' => $case->uuid,
            'resource_type' => 'covid-case',
            'resource_permission' => ResourcePermission::edit(),
        ]);

        $response->assertOk();
        $response->assertJson([
            'organisationUuid' => $organisation->uuid,
        ]);
    }

    public function testCanCreateCallToActionAsCallCenterWithToken(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $organisation = $user->getOrganisation();
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation);
        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $subject = $this->faker->word(),
            'description' => $description = $this->faker->sentence(),
            'organisation_uuid' => $organisation->uuid,
            'resource_uuid' => $case->uuid,
            'resource_type' => 'covid-case',
            'resource_permission' => ResourcePermission::edit(),
            'expires_at' => null,
        ], [
            'Assignment-Token' => $this->assignmentTokenService->createToken(
                Collection::make([
                    new TokenResource(mod: AssignmentModelEnum::CallToAction, ids: [$case->uuid]),
                ]),
                $user,
            ),
        ]);

        $response->assertOk();
        $response->assertJson([
            'subject' => $subject,
            'description' => $description,
            'organisationUuid' => $organisation->uuid,
        ]);
    }

    public function testCanCreateCallToActionAsCallCenterWithoutToken(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $organisation = $user->getOrganisation();
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation);
        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'organisation_uuid' => $organisation->uuid,
            'resource_uuid' => $case->uuid,
            'resource_type' => 'covid-case',
            'resource_permission' => ResourcePermission::edit(),
            'expires_at' => null,
        ]);

        $response->assertForbidden();
    }

    public function testCanCreateCallToActionAsCallCenterWithInvalidToken(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $organisation = $user->getOrganisation();
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation);
        $response = $this->putJson(self::BASE_PATH, [
            'subject' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'organisation_uuid' => $organisation->uuid,
            'resource_uuid' => $case->uuid,
            'resource_type' => 'covid-case',
            'resource_permission' => ResourcePermission::edit(),
            'expires_at' => null,
        ], [
            'Assignment-Token' => $this->assignmentTokenService->createToken(
                Collection::make([
                    new TokenResource(mod: AssignmentModelEnum::CallToAction, ids: [$this->faker->uuid]),
                ]),
                $user,
            ),
        ]);

        $response->assertForbidden();
    }

    public function testCanPickupCallToAction(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $this->be($user);
        $callToActionResource = $this->createResourceForCallToAction();

        $chore = $this->createChore([
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->post(self::BASE_PATH . '/' . $callToActionResource->id . '/pickup', ['expires_at' => null]);
        $response->assertOk();

        $this->assertDatabaseHas('assignment', [
            'user_uuid' => $user->uuid,
            'chore_uuid' => $chore->uuid,
        ]);
    }

    public function testCannotPickupCallToActionFromAnotherOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUser();
        $this->be($user);
        $callToActionResource = $this->createResourceForCallToAction();

        $chore = $this->createChore([
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->post(self::BASE_PATH . '/' . $callToActionResource->id . '/pickup', ['expires_at' => null]);
        $response->assertStatus(Response::HTTP_NOT_FOUND); // Case cannot be found and such cannot be picked up

        $this->assertDatabaseMissing('assignment', [
            'user_uuid' => $user->uuid,
            'chore_uuid' => $chore->uuid,
        ]);
    }

    public function testCanDropCallToAction(): void
    {
        [$user, $callToActionResource, $chore, $assignment] = $this->assignUserToCallToAction();

        $response = $this->dropCallToActionWithNote($callToActionResource);
        $response->assertNoContent();

        $this->assertDatabaseHas('call_to_action_note', [
            'user_uuid' => $user->uuid,
            'call_to_action_uuid' => $callToActionResource->id,
        ]);
        $this->assertNotSoftDeleted('chore', ['uuid' => $chore->uuid]);
        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testCannotDropCallToActionWithoutNote(): void
    {
        [$user, $resource, $chore, $assignment] = $this->assignUserToCallToAction();

        $response = $this->postJson(self::BASE_PATH . '/' . $resource->id . '/drop');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('note');

        $this->assertNotSoftDeleted('chore', [
            'uuid' => $chore->uuid,
            'organisation_uuid' => $user->getOrganisation()->uuid,
        ]);
        $this->assertNotSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testCannotDropCallToActionWhenNotAssigned(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $resource = $this->createResourceForCallToAction();
        $this->createChoreWithUserAndResource($user, $resource);

        $response = $this->dropCallToActionWithNote($resource);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testCanCompleteCallToAction(): void
    {
        [$user, $resource, $chore, $assignment] = $this->assignUserToCallToAction();

        $response = $this->completeCallToActionWithNote($resource);
        $response->assertNoContent();

        $this->assertDatabaseHas('call_to_action_note', [
            'user_uuid' => $user->uuid,
            'call_to_action_uuid' => $resource->id,
        ]);
        $this->assertSoftDeleted('chore', ['uuid' => $chore->uuid]);
        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testCannotCompleteCallToActionWithoutNote(): void
    {
        [$user, $resource, $chore, $assignment] = $this->assignUserToCallToAction();

        $response = $this->postJson(self::BASE_PATH . '/' . $resource->id . '/complete');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('note');

        $this->assertNotSoftDeleted('chore', [
            'uuid' => $chore->uuid,
            'organisation_uuid' => $user->getOrganisation()->uuid,
        ]);
        $this->assertNotSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testCannotCompleteCallToActionWhenNotAssigned(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $resource = $this->createResourceForCallToAction();
        $this->createChoreWithUserAndResource($user, $resource);

        $response = $this->completeCallToActionWithNote($resource);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    private function createUserWithOrganisationAndLogin(): EloquentUser
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        return $user;
    }

    private function dropCallToActionWithNote(Resource $resource): TestResponse
    {
        $note = $this->faker->text();

        return $this->postJson(self::BASE_PATH . '/' . $resource->id . '/drop', ['note' => $note]);
    }

    private function completeCallToActionWithNote(Resource $resource): TestResponse
    {
        $note = $this->faker->text();

        return $this->postJson(self::BASE_PATH . '/' . $resource->id . '/complete', ['note' => $note]);
    }

    private function createChoreWithUserAndResource(EloquentUser $user, Resource $resource): Chore
    {
        return $this->createChore([
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
            'organisation_uuid' => $user->getOrganisation()->uuid,
        ]);
    }

    private function assignChoreToUser(Chore $chore, EloquentUser $user): Assignment
    {
        return $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
            'expires_at' => CarbonImmutable::now()->addDay(),
        ]);
    }

    private function assignUserToCallToAction(): array
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $resource = $this->createResourceForCallToAction();
        $chore = $this->createChoreWithUserAndResource($user, $resource);
        $assignment = $this->assignChoreToUser($chore, $user);

        return [$user, $resource, $chore, $assignment];
    }

    public function testListCallToActionHistoryWhenNotLoggedInIsForbidden(): void
    {
        $this->getJson(self::BASE_PATH . '/' . $this->faker->uuid() . '/history')->assertUnauthorized();
    }

    public function testGetCallToActionWhenLoggedInWithoutPermissionIsForbidden(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], ''));

        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        $this->getJson(self::BASE_PATH . '/' . $callToAction->uuid)->assertForbidden();
    }

    public function testGetCallToActionHistoryWhenLoggedInWithoutPermissionIsForbidden(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], ''));

        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        $this->getJson(self::BASE_PATH . '/' . $callToAction->uuid . '/history')->assertForbidden();
    }

    public function testGetCallToActionWhichIsNotConnectedToChoreIsNotFound(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], ''));

        $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $this->createResourceForCallToAction($callToAction);

        $this->getJson(self::BASE_PATH . '/' . $callToAction->uuid)->assertNotFound();
    }

    public function testListCallToAcionHistoryNotFound(): void
    {
        $this->createUserWithOrganisationAndLogin();
        $response = $this->getJson(self::BASE_PATH . '/0/history');
        $response->assertNotFound();
    }

    public function testListCallToActionHistory(): void
    {
        $organisation1 = $this->createOrganisation();
        $user1 = $this->createUserForOrganisation($organisation1);
        $this->be($user1);

        $callToActionResource = $this->createResourceForCallToAction();

        $chore = $this->createChore([
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
            'organisation_uuid' => $organisation1->uuid,
        ]);

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user1->uuid,
            'created_at' => CarbonImmutable::now()->subHour(),
        ]);

        $note1 = $this->faker->text();
        $this->postJson(self::BASE_PATH . '/' . $callToActionResource->id . '/drop', ['note' => $note1]);

        $user2 = $this->createUserForOrganisation($organisation1);
        $this->be($user2);

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user2->uuid,
            'created_at' => CarbonImmutable::now(),
        ]);

        $note2 = $this->faker->text();
        $this->postJson(self::BASE_PATH . '/' . $callToActionResource->id . '/complete', ['note' => $note2]);

        $response = $this->getJson(self::BASE_PATH . '/' . $callToActionResource->id . '/history');

        $events = $response->json('events');

        self::assertNotNull($response->json('deletedAt'));
        self::assertCount(6, $events);

        self::assertEqualsCanonicalizing([
            CallToActionEvent::completed()->value,
            CallToActionEvent::returned()->value,
            CallToActionEvent::note()->value,
            CallToActionEvent::pickedUp()->value,
        ], collect($events)->pluck('callToActionEvent')->unique()->toArray());

        $expectedUsernames = [$user1->name, $user2->name];
        $actualUsernames = collect($events)->pluck('user.name')->unique()->toArray();

        self::assertEqualsCanonicalizing($expectedUsernames, $actualUsernames);
    }

    /**
     * @see https://egeniq.atlassian.net/browse/DBCO-5188
     */
    public function testListUniqueCallToActionsGivenMultipleAssignments(): void
    {
        $organisation1 = $this->createOrganisation();
        $user1 = $this->createUserForOrganisation($organisation1);
        $this->be($user1);

        $callToActionResource = $this->createResourceForCallToAction();

        $chore = $this->createChore([
            'owner_resource_type' => $callToActionResource->type,
            'owner_resource_id' => $callToActionResource->id,
            'organisation_uuid' => $organisation1->uuid,
        ]);

        $assignment = $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user1->uuid,
            'created_at' => CarbonImmutable::now()->subHour(),
        ]);

        $assignment->delete();

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user1->uuid,
            'created_at' => CarbonImmutable::now()->subHour(),
        ]);

        $response = $this->get(self::BASE_PATH);
        $response->assertOk();

        self::assertEquals(1, $response->json('total'));
    }
}

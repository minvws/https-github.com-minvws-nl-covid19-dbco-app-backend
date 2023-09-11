<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Chores;

use App\Dto\Chore\Resource;
use App\Services\Chores\ChoreService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\AuthenticationException;
use MinVWS\DBCO\Enum\Models\ResourcePermission;
use Tests\Feature\FeatureTestCase;

use function app;

class ChoreServiceTest extends FeatureTestCase
{
    /**
     * @var ResourcePermission $permissionClass;
     *
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    private string $permissionClass = ResourcePermission::class;

    public const DAYS_VISIBLE_AFTER_EXPIRY = 14;

    private ChoreService $choreService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->choreService = app(ChoreService::class);
    }

    public function testCreateChoreTestWithCaseForTask(): void
    {
        // Setup mock data
        $organisation = $this->createOrganisation();
        $resource = $this->createResourceForCase();
        $ownerResource = $this->createResourceForTask();
        $permission = $this->permissionClass::edit();

        // Create the chore though the service
        $response = $this->choreService->createChore($organisation->uuid, $resource, $ownerResource, $permission, null);

        // Response should be string & uuid
        $this->assertIsString($response);

        // Assert that correct data has been set within the database
        $this->assertDatabaseHas('chore', [
            'uuid' => $response,
            'organisation_uuid' => $organisation->uuid,
            'resource_type' => $resource->type,
            'resource_id' => $resource->id,
            'resource_permission' => $permission->value,
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'expires_at' => null,
        ]);
    }

    public function testAssignChore(): void
    {
        $chore = $this->createChore();
        $user = $this->createUser();

        $response = $this->choreService->assignChore($chore->uuid, $user->uuid, null);

        $this->assertIsString($response);

        $this->assertDatabaseHas('assignment', [
            'uuid' => $response,
            'user_uuid' => $user->uuid,
            'chore_uuid' => $chore->uuid,
        ]);
    }

    /**
     * @throws Exception
     */
    public function testAssignChoreFailsIfAlreadyAssigned(): void
    {
        $chore = $this->createChore();
        $user = $this->createUser();

        $this->createAssignment(['chore_uuid' => $chore->uuid]);

        // Test will pass if the exception code has been given after the 'expectExceptionCode' method
        $this->expectExceptionCode(400);
        $this->choreService->assignChore($chore->uuid, $user->uuid, null);
    }

    /**
     * @throws Exception
     */
    public function testAssignChorePassesIfAlreadyAssignedWithDeletedAssignment(): void
    {
        $chore = $this->createChore();
        $user = $this->createUser();

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'deleted_at' => $this->faker->dateTime(),
        ]);

        // Test will pass if the exception code has been given after the 'expectExceptionCode' method
        $response = $this->choreService->assignChore($chore->uuid, $user->uuid, null);

        $this->assertIsString($response);

        $this->assertDatabaseHas('assignment', [
            'uuid' => $response,
            'user_uuid' => $user->uuid,
            'chore_uuid' => $chore->uuid,
        ]);
    }

    public function testCompleteChore(): void
    {
        $chore = $this->createChore();
        $assignment = $this->createAssignment(['chore_uuid' => $chore->uuid]);

        $this->choreService->completeChore($chore->uuid);

        $this->assertSoftDeleted('chore', ['uuid' => $chore->uuid]);
        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testCancelChore(): void
    {
        $chore = $this->createChore();
        $assignment = $this->createAssignment(['chore_uuid' => $chore->uuid]);

        $this->choreService->cancelChore($chore->uuid);

        $this->assertSoftDeleted('chore', ['uuid' => $chore->uuid]);
        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testCancelAssignment(): void
    {
        $chore = $this->createChore();
        $assignment = $this->createAssignment(['chore_uuid' => $chore->uuid]);

        $this->choreService->cancelAssignment($assignment->uuid);

        // Make sure assignment is softDeleted & chore not
        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
        $this->assertNotSoftDeleted('chore', ['uuid' => $chore->uuid]);
    }

    public function testCleanupExpiredChoresAndAssignments(): void
    {
        $upToDateChore = $this->createChore(['expires_at' => CarbonImmutable::now()->addDay()]);
        $expiredChore = $this->createChore(['expires_at' => CarbonImmutable::now()->subDay()]);

        $upToDateAssignment = $this->createAssignment(['expires_at' => CarbonImmutable::now()->addDay()]);
        $expiredAssignment = $this->createAssignment(['expires_at' => CarbonImmutable::now()->subDay()]);

        $this->choreService->cleanupExpiredChoresAndAssignments();

        $this->assertNotSoftDeleted('chore', ['uuid' => $upToDateChore->uuid]);
        $this->assertSoftDeleted('chore', ['uuid' => $expiredChore->uuid]);

        $this->assertNotSoftDeleted('assignment', ['uuid' => $upToDateAssignment->uuid]);
        $this->assertSoftDeleted('assignment', ['uuid' => $expiredAssignment->uuid]);
    }

    public function testChangeOrganisationForChore(): void
    {
        $initialOrganisation = $this->createOrganisation();
        $updateOrganisation = $this->createOrganisation();
        $chore = $this->createChoreForOrganisation($initialOrganisation);

        $this->choreService->updateOrganisation($chore->uuid, $updateOrganisation->uuid);

        $this->assertDatabaseHas('chore', [
            'uuid' => $chore->uuid,
            'organisation_uuid' => $updateOrganisation->uuid,
        ]);
    }

    public function testChangeOrganisationForChoreRemovesAssignments(): void
    {
        $initialOrganisation = $this->createOrganisation();
        $updateOrganisation = $this->createOrganisation();
        $chore = $this->createChoreForOrganisation($initialOrganisation);
        $assignment = $this->createAssignmentForChore($chore);

        $this->choreService->updateOrganisation($chore->uuid, $updateOrganisation->uuid);

        $this->assertNotSoftDeleted('chore', ['uuid' => $chore->uuid]);
        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    public function testChangeOrganisationForChoreWithTheSameOrganisationWillNotRemoveAssignments(): void
    {
        $organisation = $this->createOrganisation();
        $chore = $this->createChoreForOrganisation($organisation);
        $assignment = $this->createAssignmentForChore($chore);

        $this->choreService->updateOrganisation($chore->uuid, $organisation->uuid);

        $this->assertNotSoftDeleted('chore', ['uuid' => $chore->uuid]);
        $this->assertNotSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanAccessResourceWithViewPermissionAndAssignment(): void
    {
        $this->be($user = $this->createUser());

        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::view(),
        ]);

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $this->assertTrue($this->choreService->canAccessResource(ResourcePermission::view(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanAccessResourceWithEditPermissionAndAssignment(): void
    {
        $this->be($user = $this->createUser());
        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::edit(),
        ]);

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $this->assertTrue($this->choreService->canAccessResource(ResourcePermission::edit(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanNotAccessResourceWithIncorrectPermission(): void
    {
        $this->be($user = $this->createUser());
        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::view(),
        ]);

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $this->assertFalse($this->choreService->canAccessResource(ResourcePermission::edit(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanNotAccessResourceWithoutAssignment(): void
    {
        $this->be($user = $this->createUser());
        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::view(),
        ]);

        $this->assertFalse($this->choreService->canAccessResource(ResourcePermission::edit(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanNotAccessResourceWithAssignmentToAnotherUser(): void
    {
        $this->be($this->createUser());
        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::view(),
        ]);
        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $this->createUser()->uuid,
        ]);

        $this->assertFalse($this->choreService->canAccessResource(ResourcePermission::edit(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanNotAccessResourceWithDeletedAssignment(): void
    {
        $this->be($user = $this->createUser());
        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::view(),
        ]);
        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
            'deleted_at' => $this->faker->dateTime(),
        ]);

        $this->assertFalse($this->choreService->canAccessResource(ResourcePermission::edit(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanNotAccessResourceWithExpiredChore(): void
    {
        $this->be($user = $this->createUser());
        $chore = $this->createChore([
            'resource_permission' => ResourcePermission::view(),
            'expires_at' => CarbonImmutable::now()->addDays(self::DAYS_VISIBLE_AFTER_EXPIRY + 1),
        ]);

        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $this->assertFalse($this->choreService->canAccessResource(ResourcePermission::edit(), $chore->resource));
    }

    /**
     * @throws AuthenticationException
     */
    public function testUserCanNotAccessResourceIfChoreDoesNotExists(): void
    {
        $this->be($this->createUser());

        $this->assertFalse(
            $this->choreService->canAccessResource(ResourcePermission::edit(), new Resource($this->faker->word(), $this->faker->uuid)),
        );
    }
}

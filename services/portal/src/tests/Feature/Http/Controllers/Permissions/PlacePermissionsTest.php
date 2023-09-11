<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Permissions;

use MinVWS\DBCO\Enum\Models\ContextCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\FeatureTestCase;

use function array_diff;
use function array_map;

/**
 * @see \Tests\Feature\Policies\PlacePolicyTest
 * Use that to test the detailed policy rules.
 * Use this test for other rules & to check if the policy is applied at the right places
 */
class PlacePermissionsTest extends FeatureTestCase
{
    private const ALL_ROLES = [
        'user',
        'user_nationwide',
        'planner',
        'planner_nationwide',
        'compliance',
        'contextmanager',
        'clusterspecialist',
        'casequality',
        'casequality_nationwide',
        'conversation_coach',
        'conversation_coach_nationwide',
        'medical_supervisor',
        'medical_supervisor_nationwide',
        'callcenter',
    ];

    private const CAN_EDIT_PLACE_ROLES = [
        'user',
        'user_nationwide',
        'clusterspecialist',
        'casequality',
        'casequality_nationwide',
    ];

    private const CAN_LIST_OR_MERGE_PLACE_ROLES = [
        'clusterspecialist',
    ];

    private const CAN_GET_PLACES_ROLES = [
        'user',
        'user_nationwide',
        'casequality',
        'casequality_nationwide',
        'clusterspecialist',
        'clusterspecialist_nationwide',
    ];

    #[DataProvider('placeEditRolesProvider')]
    #[TestDox('User with role $role can create a place')]
    public function testUsersWithPlaceCreatePermissionCanCreatePlaces(string $role): void
    {
        $user = $this->createUser([], $role);

        $this->be($user)->postJson('api/places', [
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
        ])->assertStatus(201);
    }

    public static function placeEditRolesProvider(): array
    {
        return self::convertRolesToRolesProvider(self::CAN_EDIT_PLACE_ROLES);
    }

    #[DataProvider('rolesThatCannotEditPlaces')]
    #[TestDox('User with role $role can NOT create a place')]
    public function testUsersWithoutPlaceCreatePermissionCannotCreatePlaces(string $role): void
    {
        $user = $this->createUser([], $role);

        $this->be($user)->postJson('api/places', [
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
        ])->assertStatus(403);
    }

    public static function rolesThatCannotEditPlaces(): array
    {
        return self::convertRolesToRolesProvider(array_diff(self::ALL_ROLES, self::CAN_EDIT_PLACE_ROLES));
    }

    #[DataProvider('placeEditRolesProvider')]
    #[TestDox('User with role $role can search places')]
    public function testUsersWithPlaceSearchPermissionCanSearchPlaces(string $role): void
    {
        $user = $this->createUser([], $role);

        $this->be($user)->getJson('api/places/search')->assertStatus(200);
        $this->be($user)->postJson('api/places/search')->assertStatus(200);
        $this->be($user)->getJson('api/places/search/similar')->assertStatus(200);
        $this->be($user)->postJson('api/places/search/similar')->assertStatus(200);
    }

    #[DataProvider('rolesThatCannotEditPlaces')]
    #[TestDox('User with role $role can NOT search places')]
    public function testUsersWithoutPlaceSearchPermissionCannotSearchPlaces(string $role): void
    {
        $user = $this->createUser([], $role);

        $this->be($user)->getJson('api/places/search')->assertStatus(403);
        $this->be($user)->postJson('api/places/search')->assertStatus(403);
        $this->be($user)->getJson('api/places/search/similar')->assertStatus(403);
        $this->be($user)->postJson('api/places/search/similar')->assertStatus(403);
    }

    public function testCannotVerifyPlaceFromOtherRegion(): void
    {
        $userOrg = $this->createOrganisation();
        $placeOrg = $this->createOrganisation();
        $user = $this->createUserForOrganisation($userOrg, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlace(['is_verified' => false, 'organisation_uuid' => $placeOrg->uuid]);

        $response = $this->putJson('api/places/' . $place->uuid . '/verify');
        $response->assertStatus(403);
    }

    #[DataProvider('allRolesDataProvider')]
    #[TestDox('User with role $roles can NOT verify place without permission')]
    public function testCanNotVerifyWithoutPermission(string $roles): void
    {
        $user = $this->createUser([], $roles);
        $this->be($user);
        $place = $this->createPlace(['is_verified' => false]);

        $response = $this->putJson('api/places/' . $place->uuid . '/verify');
        $response->assertStatus(403);
    }

    #[DataProvider('allRolesDataProvider')]
    #[TestDox('User with role $roles can NOT un-verify place without permission')]
    public function testCanNotUnVerifyWithoutPermission(string $roles): void
    {
        $user = $this->createUser([], $roles);
        $this->be($user);
        $place = $this->createPlace(['is_verified' => true]);

        $response = $this->putJson('api/places/' . $place->uuid . '/unverify');
        $response->assertStatus(403);
    }

    #[DataProvider('allRolesDataProvider')]
    #[TestDox('User with role $roles can NOT verify multiple places without permission')]
    public function testCanNotVerifyMultiWithoutPermission(string $roles): void
    {
        $user = $this->createUser([], $roles);
        $this->be($user);
        $place = $this->createPlace(['is_verified' => false]);

        $response = $this->putJson('api/places/verifyMulti', ['placeUuids' => [$place->uuid]]);
        $response->assertStatus(422);
    }

    public static function allRolesDataProvider(): array
    {
        return self::convertRolesToRolesProvider(self::ALL_ROLES);
    }

    public function testCannotUnVerifyPlaceFromOtherRegion(): void
    {
        $userOrg = $this->createOrganisation();
        $placeOrg = $this->createOrganisation();
        $user = $this->createUserForOrganisation($userOrg, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlace(['is_verified' => true, 'organisation_uuid' => $placeOrg->uuid]);

        $response = $this->putJson('api/places/' . $place->uuid . '/unverify');
        $response->assertStatus(403);
    }

    #[DataProvider('rolesThatCannotGetPlaces')]
    #[TestDox('User with role $role can NOT get a place')]
    public function testUsersWithoutCaseEditPermissionCannotGetPlace(string $role): void
    {
        $user = $this->createUser([], $role);
        $place = $this->createPlace();

        $this->be($user)->getJson('api/places/' . $place->uuid)->assertStatus(403);
    }

    public static function rolesThatCannotGetPlaces(): array
    {
        return self::convertRolesToRolesProvider(array_diff(self::ALL_ROLES, self::CAN_GET_PLACES_ROLES));
    }

    /**
     * @see \Tests\Feature\Policies\PlacePolicyTest for detailed tests, this only checks if the policy is applied
     */
    #[DataProvider('rolesThatCannotEditPlaces')]
    #[TestDox('User with role $role can NOT update a place')]
    public function testUsersWithoutPlaceEditPermissionCannotEditPlace(string $role): void
    {
        $user = $this->createUser([], $role);
        $place = $this->createPlace();

        $this->be($user)->putJson('api/places/' . $place->uuid, [
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
        ])->assertStatus(403);
    }

    /**
     * @see \Tests\Feature\Policies\PlacePolicyTest for detailed tests, this only checks if the policy is applied
     */
    #[DataProvider('rolesThatCannotMergePlaces')]
    #[TestDox('User with role $role can NOT merge places')]
    public function testUsersWithoutPlaceMergePermissionCannotMergePlace(string $role): void
    {
        $user = $this->createUser([], $role);
        $place = $this->createPlace();
        $placeToMerge = $this->createPlace();

        $this->be($user)->putJson("api/places/{$place->uuid}/merge", [
            'merge_places' => [$placeToMerge->uuid],
        ])->assertStatus(403);
    }

    public static function rolesThatCannotMergePlaces(): array
    {
        return self::convertRolesToRolesProvider(array_diff(self::ALL_ROLES, self::CAN_LIST_OR_MERGE_PLACE_ROLES));
    }

    /**
     * @see \Tests\Feature\Policies\PlacePolicyTest for detailed tests, this only checks if the policy is applied
     */
    #[TestDox('Assert GET place sections applies PlacePolicy')]
    public function testGetSectionPermissionsAreApplied(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $place = $this->createPlace();

        $this->be($user)->getJson("api/places/$place->uuid/sections")->assertStatus(403);
    }

    /**
     * @see \Tests\Feature\Policies\PlacePolicyTest for detailed tests, this only checks if the policy is applied
     */
    #[TestDox('Assert PUT place sections applies PlacePolicy')]
    public function testPutSectionPermissionsAreApplied(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $place = $this->createPlace();

        $this->be($user)->putJson(
            "api/places/$place->uuid/sections",
            ['sections' => ['section' => ['label' => 'Kantoor']]],
        )->assertStatus(403);
    }

    /**
     * @see \Tests\Feature\Policies\PlacePolicyTest for detailed tests, this only checks if the policy is applied
     */
    #[TestDox('Assert PATCH place sections applies PlacePolicy')]
    public function testPatchSectionPermissionsAreApplied(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $place = $this->createPlace();

        $this->be($user)->patchJson(
            "api/places/$place->uuid/sections",
            ['sections' => ['section' => ['label' => 'Kantoor']]],
        )->assertStatus(403);
    }

    /**
     * @see \Tests\Feature\Policies\PlacePolicyTest for detailed tests, this only checks if the policy is applied
     */
    #[TestDox('Assert MERGE place sections applies PlacePolicy')]
    public function testMergeSectionPermissionsAreApplied(): void
    {
        $user = $this->createUser([], 'medical_supervisor');
        $place = $this->createPlace();
        $section = $this->createSectionForPlace($place);
        $sectionToMerge = $this->createSectionForPlace($place);

        $this->be($user)->postJson(
            "api/places/$place->uuid/sections/$section->uuid/merge",
            ['merge_sections' => [$sectionToMerge->uuid]],
        )->assertStatus(403);
    }

    private static function convertRolesToRolesProvider(array $roles): array
    {
        return array_map(static fn($element) => [$element], $roles);
    }
}

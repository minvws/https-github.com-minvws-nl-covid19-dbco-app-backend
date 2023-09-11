<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Policies\PlacePolicy;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('authorization')]
class PlacePolicyTest extends FeatureTestCase
{
    private PlacePolicy $placePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->placePolicy = app(PlacePolicy::class);
    }

    #[DataProvider('policyDataProvider')]
    public function testPolicy(
        string $role,
        bool $ownedByOrganisation,
        array $array,
    ): void {
        if ($ownedByOrganisation) {
            [$user, $place] = $this->createOwnedByOrganisationUserPlace($role);
        } else {
            [$user, $place] = $this->createNotOwnedByOrganisationUserPlace($role);
        }

        foreach ($array as $values) {
            $assert = $values[0];
            $method = $values[1];

            $this->assertEquals($assert, $this->placePolicy->$method($user, $place), "`{$role}` tries to access method `{$method}`");
        }
    }

    public static function policyDataProvider(): Generator
    {
        yield 'With role `user` & owned by organisation place' => [
            'user',
            true,
            [
                [false, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `user` & not owned by organisation place' => [
            'user',
            false,
            [
                [false, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `user_nationwide` & owned by organisation place' => [
            'user_nationwide',
            true,
            [
                [false, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `user_nationwide` & not owned by organisation place' => [
            'user_nationwide',
            false,
            [
                [false, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `casequality` & owned by organisation place' => [
            'casequality',
            true,
            [
                [false, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `casequality` & not owned by organisation place' => [
            'casequality',
            false,
            [
                [false, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `contextmanager` & owned by organisation place' => [
            'contextmanager',
            true,
            [
                [false, 'list'],
                [false, 'create'],
                [false, 'edit'],
                [false, 'delete'], // indexen
                [false, 'search'],
                [false, 'merge'],
                [false, 'sectionList'],
                [false, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `contextmanager` & not owned by organisation place' => [
            'contextmanager',
            false,
            [
                [false, 'list'],
                [false, 'create'],
                [false, 'edit'],
                [false, 'delete'],
                [false, 'search'],
                [false, 'merge'],
                [false, 'sectionList'],
                [false, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];

        yield 'With role `clusterspecialist` & owned by organisation place' => [
            'clusterspecialist',
            true,
            [
                [true, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [true, 'delete'],
                [true, 'search'],
                [true, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [true, 'sectionEdit'],
                [true, 'sectionDelete'],
                [true, 'sectionMerge'],
            ],
        ];

        yield 'With role `clusterspecialist` & not owned by organisation place' => [
            'clusterspecialist',
            false,
            [
                [true, 'list'],
                [true, 'create'],
                [true, 'edit'],
                [false, 'delete'],
                [true, 'search'],
                [false, 'merge'],
                [true, 'sectionList'],
                [true, 'sectionCreate'],
                [false, 'sectionEdit'],
                [false, 'sectionDelete'],
                [false, 'sectionMerge'],
            ],
        ];
    }

    private function createOwnedByOrganisationUserPlace(string $role): array
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $role);
        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);

         return [$user, $place];
    }

    private function createNotOwnedByOrganisationUserPlace(string $role): array
    {
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();

        $user = $this->createUserForOrganisation($organisationOne, [], $role);

        $place = $this->createPlace(['organisation_uuid' => $organisationTwo->uuid]);

        return [$user, $place];
    }
}

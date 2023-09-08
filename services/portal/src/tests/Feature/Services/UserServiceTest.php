<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\OrganisationType;
use App\Providers\Auth\IdentityHubUser;
use App\Services\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

use function app;
use function array_merge;
use function config;

class UserServiceTest extends FeatureTestCase
{
    #[DataProvider('mappingProvider')]
    public function testSetsNoRolesOnInvalidRoleMapping(
        array $suppliedRoles,
        array $organisationAttributes,
        string $expectedRoles,
    ): void {
        config()->set('authorization.roles', [
            'user' => 'DBCO-Gebruiker',
            'user_nationwide' => 'BCO-Landelijk-Gebruiker',
        ]);

        /** @var UserService $userService */
        $userService = app(UserService::class);

        $externalId = Uuid::uuid4()->toString();
        $organisation = $this->createOrganisation(array_merge(['external_id' => $externalId], $organisationAttributes));

        $user = $userService->upsertUserByOrganisationUuid(
            $this->createIdentityHubUserUser($externalId, $suppliedRoles),
            $organisation->uuid,
        );
        $this->assertEquals($expectedRoles, $user->roles);
    }

    public static function mappingProvider(): array
    {
        return [
            "on regionalGGD with landelijk role" => [
                [['name' => 'BCO-Landelijk-Gebruiker']],
                ['type' => OrganisationType::regionalGGD()],
                '',
            ],
            "even when a valid role is available" => [
                [['name' => 'DBCO-Gebruiker'], ['name' => 'BCO-Landelijk-Gebruiker']],
                ['type' => OrganisationType::regionalGGD()],
                '',
            ],
            "on outsourceOrganisation with regional role" => [
                [['name' => 'DBCO-Gebruiker']],
                ['type' => OrganisationType::outsourceOrganisation()],
                '',
            ],
            "on outsourceDepartment with regional role" => [
                [['name' => 'DBCO-Gebruiker']],
                ['type' => OrganisationType::outsourceOrganisation()],
                '',
            ],
            "not with valid combination" => [
                [['name' => 'DBCO-Gebruiker']],
                ['type' => OrganisationType::regionalGGD()],
                'user',
            ],
        ];
    }

    private function createIdentityHubUserUser(string $organisationUuid, array $roles): IdentityHubUser
    {
        $userObj = new IdentityHubUser();
        $userObj->id = '123';
        $userObj->name = 'User #1';
        $userObj->organisations = [$organisationUuid];
        $userObj->roles = $roles;
        return $userObj;
    }
}

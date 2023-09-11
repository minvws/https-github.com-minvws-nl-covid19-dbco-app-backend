<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_diff;
use function array_keys;
use function collect;
use function config;
use function implode;
use function str_replace;
use function strpos;

/**
 * Class AuthorizationTest
 */
#[Group('authorization')]
class AuthorizationTest extends FeatureTestCase
{
    #[DataProvider('userNotAuthorizedProvider')]
    #[DataProvider('plannerNotAuthorizedProvider')]
    #[DataProvider('complianceNotAuthorizedProvider')]
    public function testUserNotAuthorized(string $roles, string $method, string $route): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], $roles);

        $response = $this->be($user)->$method($route);
        $response->assertStatus(403);
    }

    public static function userNotAuthorizedProvider(): array
    {
        return [
            'web: user lists planner cases' => ['user', 'get', '/planner'],
            'api: user get list planner cases' => ['user', 'getJson', '/api/cases/unassigned'],
            'web: user lists accessrequests cases' => ['user', 'get', '/compliance'],
            'web: user get search accessrequests cases' => ['user', 'get', '/compliance/search'],
            'api: user post list accessrequests cases' => ['user', 'postJson', '/api/search'],
        ];
    }

    public static function plannerNotAuthorizedProvider(): array
    {
        return [
            'web: planner lists user cases' => ['planner', 'get', '/cases'],
            'web: planner lists accessrequests cases' => ['planner', 'get', '/compliance'],
            'web: planner get search accessrequests cases' => ['planner', 'get', '/compliance/search'],
            'api: planner post list accessrequests cases' => ['planner', 'postJson', '/api/search'],
        ];
    }

    public static function complianceNotAuthorizedProvider(): array
    {
        return [
            'web: compliance lists user cases' => ['compliance', 'get', '/cases'],
            'api: compliance creates case' => ['compliance', 'postJson', '/api/cases'],
            'web: compliance lists planner cases' => ['compliance', 'get', '/planner'],
        ];
    }

    #[DataProvider('accessDataProvider')]
    public function testAllowedAccess(string $roles, string $method, string $route, int $expectedStatusCode): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], $roles);

        $case = $this->createCaseForUser($user);
        $route = str_replace('{case}', $case->uuid, $route);

        $response = $this->be($user)->$method($route);
        $response->assertStatus($expectedStatusCode);
    }

    public static function accessDataProvider(): array
    {
        return [
            // api/cases/{case}/validation-status
            'api: user case validation status' => ['user', 'getJson', 'api/cases/{case}/validation-status', 200],
            'api: clusterspecialist case validation status' => ['clusterspecialist', 'getJson', 'api/cases/{case}/validation-status', 200],
            'api: planner case validation status' => ['planner', 'getJson', 'api/cases/{case}/validation-status', 403],
            'api: compliance case validation status' => ['compliance', 'getJson', 'api/cases/{case}/validation-status', 403],
        ];
    }

    #[DataProvider('userNoAccessToCovidCaseProvider')]
    #[DataProvider('plannerNoAccessToCovidCaseProvider')]
    #[DataProvider('complianceNoAccessToCovidCaseProvider')]
    public function testUserCannotAccessCovidCase(string $roles, string $method, string $route): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], $roles);

        $case = $this->createCaseForUser($user);
        $route = str_replace('{case}', $case->uuid, $route);

        $response = $this->be($user)->$method($route);
        $response->assertStatus(403);
    }

    public static function userNoAccessToCovidCaseProvider(): array
    {
        return [
            'api: user download accessrequests case fragments' => ['user', 'get', 'api/access-requests/case/{case}/fragments'],
            'api: user download accessrequests case pdf' => ['user', 'get', 'api/access-requests/case/{case}/download'],
            'api: user download accessrequests case html' => ['user', 'get', 'api/access-requests/case/{case}/download/html'],
            'api: user delete case' => ['user', 'deleteJson', 'api/cases/{case}'],
            'api: user delete compliance case' => ['user', 'deleteJson', 'api/access-requests/case/{case}'],
        ];
    }

    public static function plannerNoAccessToCovidCaseProvider(): array
    {
        return [
            'api: planner download accessrequests case fragments' => ['planner', 'get', 'api/access-requests/case/{case}/fragments'],
            'api: planner download accessrequests case pdf' => ['planner', 'get', 'api/access-requests/case/{case}/download'],
            'api: planner download accessrequests case html' => ['planner', 'get', 'api/access-requests/case/{case}/download/html'],
            'web: planner edit case' => ['planner', 'get', '/editcase/{case}'],
            'api: planner get case' => ['planner', 'getJson', '/api/case/{case}'],
            'api: planner get fragments' => ['planner', 'getJson', '/api/cases/{case}/fragments'],
            'api: planner put fragments' => ['planner', 'putJson', '/api/cases/{case}/fragments'],
            'api: planner get fragment' => ['planner', 'getJson', '/api/cases/{case}/fragments/general'],
            'api: planner put fragment' => ['planner', 'putJson', '/api/cases/{case}/fragments/general'],
            'api: planner delete compliance case' => ['planner', 'deleteJson', 'api/access-requests/case/{case}'],
        ];
    }

    public static function complianceNoAccessToCovidCaseProvider(): array
    {
        return [
            'web: compliance edit case' => ['compliance', 'get', '/editcase/{case}'],
            'api: compliance get case' => ['compliance', 'getJson', '/api/case/{case}'],
            'api: compliance get fragments' => ['compliance', 'getJson', '/api/cases/{case}/fragments'],
            'api: compliance put fragments' => ['compliance', 'putJson', '/api/cases/{case}/fragments'],
            'api: compliance get fragment' => ['compliance', 'getJson', '/api/cases/{case}/fragments/general'],
            'api: compliance put fragment' => ['compliance', 'putJson', '/api/cases/{case}/fragments/general'],
        ];
    }

    #[DataProvider('userIsAuthorizedProvider')]
    public function testUserIsAuthorized(string $roles, string $method, string $route): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], $roles);

        $response = $this->be($user)->$method($route);
        $response->assertStatus(200);
    }

    public static function userIsAuthorizedProvider(): array
    {
        return [
            'web: user lists compliance cases' => ['admin,user,compliance', 'get', '/compliance'],
            'web: user get search accessrequests cases' => ['admin,user,compliance', 'get', '/compliance/search'],
            'api: user get list of my cases' => ['admin,user,compliance', 'getJson', '/api/cases/mine'],
            'api: user post list accessrequests cases' => ['admin,user,compliance', 'postJson', '/api/search'],
        ];
    }

    public function testNationWideUsersAreMarkedAsNationwide(): void
    {
        $allRoles = array_keys(config('authorization.roles'));
        $nationwideRoles = config('authorization.nationwide_roles');

        $diff = array_diff($allRoles, $nationwideRoles);

        $suspiciousRoles = collect($diff)->filter(static fn(string $role) => strpos($role, 'nationwide') !== false)->all();

        $this->assertEmpty(
            $suspiciousRoles,
            'roles containing "nationwide" are found that are not marked as nationwide roles. Are you sure this is correct? . Roles: ' . implode(
                ', ',
                $suspiciousRoles,
            ),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\Eloquent\EloquentUser;
use App\Models\OrganisationType;
use App\Providers\Auth\IdentityHubClient;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_merge;
use function collect;
use function config;
use function explode;
use function json_encode;
use function parse_str;
use function parse_url;
use function sprintf;
use function url;

use const PHP_URL_QUERY;

#[Group('guzzle')]
class LoginControllerTest extends FeatureTestCase
{
    public function testRedirectToProvider(): void
    {
        $response = $this->get('/auth/identityhub');
        $response->assertStatus(302);

        $url = $response->headers->get('Location');
        $queryString = parse_url($url, PHP_URL_QUERY);
        parse_str($queryString, $queryParams);

        $this->assertEquals('12345', $queryParams['client_id']);
        $this->assertEquals(url('auth/login', []), $queryParams['redirect_uri']);
    }

    public function testInvalidCallbackRequest(): void
    {
        $response = $this->get('/auth/login');
        $response->assertRedirect('/login');

        $this->assertGuest();
    }

    public function testValidRemoteSessionTime(): void
    {
        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);

        $organisation = $this->createOrganisation([
            'external_id' => 'externalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $organisation->external_id,
                        ],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(2, $historyContainer);
        $response->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function testRevokeOnSessionTimeout(): void
    {
        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $identityHubRevokeUrl = 'http://identityhub.com/oauth/revoke';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.revokeUrl', $identityHubRevokeUrl);

        CarbonImmutable::setTestNow($testNow);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(45)->format('Y-m-d\TH:i:s.v\Z'),
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // revoke session
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(3, $historyContainer);

        /** @var Request $revokeRequest */
        $revokeRequest = $historyContainer[2]['request'];
        $this->assertEquals($identityHubRevokeUrl, $revokeRequest->getUri());

        $this->assertGuest();
    }

    public function testWithDepartmentClaim(): void
    {
        $authorizationRoleUser = 'BCO-Landelijk-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';
        $departmentClaim = 'http://schemas.ggd.nl/departmentClaim';

        config()->set('authorization.roles.user_nationwide', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        config()->set('services.identityhub.claims.department', $departmentClaim);
        CarbonImmutable::setTestNow($testNow);

        $parentOrganisation = $this->createOrganisation([
            'external_id' => 'outsourceOrganisation',
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $organisation = $this->createOrganisation([
            'external_id' => 'outsourceDepartment',
            'type' => OrganisationType::outsourceDepartment(),
            'parent_organisation' => $parentOrganisation->uuid,
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $parentOrganisation->external_id,
                        ],
                        $departmentClaim => [
                            $organisation->external_id,
                        ],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(2, $historyContainer);
        $response->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function testWithDepartmentClaimAndOrganisationClaim(): void
    {
        $authorizationRoleUser = 'BCO-Landelijk-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';
        $departmentClaim = 'http://schemas.ggd.nl/departmentClaim';

        config()->set('authorization.roles.user_nationwide', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        config()->set('services.identityhub.claims.department', $departmentClaim);
        CarbonImmutable::setTestNow($testNow);

        $sosOrganisation = $this->createOrganisation([
            'external_id' => '12345',
            'type' => OrganisationType::outsourceOrganisation(),
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $sosOrganisation->external_id,
                        ],
                        $departmentClaim => [
                            'SOS',
                        ],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(2, $historyContainer);

        /** @var AuthManager $authManager */
        $authManager = $this->app->make('auth');

        $user = $authManager->guard()->user();
        $this->assertInstanceOf(EloquentUser::class, $user);
        $this->assertEquals('user_nationwide', $user->roles);

        $response->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function testWithValidDepartmentClaimButWithInvalidParentOrganisation(): void
    {
        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';
        $departmentClaim = 'http://schemas.ggd.nl/departmentClaim';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        config()->set('services.identityhub.claims.department', $departmentClaim);
        CarbonImmutable::setTestNow($testNow);

        $parentOrganisation1 = $this->createOrganisation([
            'external_id' => 'outsourceOrganisation1',
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $parentOrganisation2 = $this->createOrganisation([
            'external_id' => 'outsourceOrganisation2',
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $organisation = $this->createOrganisation([
            'external_id' => 'outsourceDepartment',
            'type' => OrganisationType::outsourceDepartment(),
            'parent_organisation' => $parentOrganisation1->uuid,
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $parentOrganisation2->external_id,
                        ],
                        $departmentClaim => [
                            $organisation->external_id,
                        ],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // revoke session
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(3, $historyContainer);
        $response->assertViewIs('no-access');

        $this->assertGuest();
    }

    /**
     * When the organisation-claim is from a regionalGgd, the department-claim (though invalid) should be ignored
     */
    public function testWithDepartmentClaimAndOrganisationClaimResultsInCorrectOrganisation(): void
    {
        $userExternalId = $this->faker->word();

        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $departmentClaim = 'http://schemas.ggd.nl/departmentClaim';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.department', $departmentClaim);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);

        $user = $this->createUser([
            'external_id' => $userExternalId,
        ]);

        $regionalOrganisation = $this->createOrganisation([
            'external_id' => 'regionalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => $userExternalId,
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $departmentClaim => [
                            'someDepartmentClaim',
                        ],
                        $organisationClaim => [
                            $regionalOrganisation->external_id,
                        ],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(2, $historyContainer);
        $response->assertRedirect('/');

        $this->assertAuthenticated();

        $user->refresh();

        $this->assertCount(1, $user->organisations);
        $this->assertEquals('regionalOrganisationId', $user->organisations[0]->external_id);
    }

    public function testUnauthorizedWithoutOrganisationOrDepartment(): void
    {
        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/ws/2020/07/identity/claims/vrregiocode';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.organisationClaim', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // revoke session
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(3, $historyContainer);
        $response->assertViewIs('no-access');

        $this->assertFalse(Auth::check());
    }

    public function testUnauthorizedWithoutRoles(): void
    {
        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/ws/2020/07/identity/claims/vrregiocode';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.organisationClaim', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            '123',
                        ],
                    ],
                ],
                'roles' => [],
            ])),
            new Response(200), // revoke session
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertCount(3, $historyContainer);
        $response->assertViewIs('no-access');

        $this->assertGuest();
    }

    #[DataProvider('identityHubRoleDataProvider')]
    public function testRoleWhenMultipleRolesConfigured(
        array $configuredRoles,
        array $organisationAttributes,
        string $identityHubRole,
        string $expectedRole,
    ): void {
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';

        config()->set('authorization.roles', $configuredRoles);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);

        $organisation = $this->createOrganisation(array_merge([
            'external_id' => 'externalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ], $organisationAttributes));

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $organisation->external_id,
                        ],
                    ],
                ],
                'roles' =>
                    collect(explode(',', $identityHubRole))->map(static fn(string $role) => ['name' => $role])
                ,
            ])),
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        /** @var AuthManager $authManager */
        $authManager = $this->app->make('auth');

        /** @var EloquentUser $user */
        $user = $authManager->guard()->user();

        $this->assertEquals($expectedRole, $user->roles);
    }

    public static function identityHubRoleDataProvider(): array
    {
        return [
            'lowercase DBCO-gebruiker' => [
                ['user' => 'DBCO-Gebruiker', 'planner' => 'DBCO-Planner'],
                [],
                'DBCO-gebruiker', // note: casing is (slightly) incorrect
                '',
            ],
            'plain DBCO-Gebruiker' => [
                ['user' => 'DBCO-Gebruiker', 'planner' => 'DBCO-Planner'],
                [],
                'DBCO-Gebruiker',
                'user',
            ],
            'landelijk BCO-Gebruiker' => [
                ['user' => 'DBCO-Gebruiker', 'user_nationwide' => 'BCO-Landelijk-Gebruiker'],
                ['type' => OrganisationType::outsourceOrganisation()],
                'BCO-Landelijk-Gebruiker',
                'user_nationwide',
            ],
            'plain DBCO-Planner' => [
                ['user' => 'DBCO-Gebruiker', 'planner' => 'DBCO-Planner'],
                [],
                'DBCO-Planner',
                'planner',
            ],
            'landelijk BCO-Planner' => [
                ['planner' => 'DBCO-Gebruiker', 'planner_nationwide' => 'BCO-Landelijk-Planner'],
                ['type' => OrganisationType::outsourceOrganisation()],
                'BCO-Landelijk-Planner',
                'planner_nationwide',
            ],
            'DBCO-Gebruiker, with two roles configured' => [
                ['user' => 'DBCO-Gebruiker,BCO-Landelijk-Gebruiker'],
                [],
                'DBCO-Gebruiker',
                'user',
            ],
            'BCO-Landelijk-Gebruiker, with two roles configured' => [
                ['user' => 'DBCO-Gebruiker,BCO-Landelijk-Gebruiker,'],
                [],
                'BCO-Landelijk-Gebruiker',
                'user',
            ],
            'combining roles on provider side should work' => [
                ['user' => 'DBCO-Gebruiker', 'planner' => 'DBCO-Planner'],
                [],
                'DBCO-Gebruiker,DBCO-Planner',
                'user,planner',
            ],
        ];
    }

    public function testCanLogInWithInvalidRoles(): void
    {
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';

        config()->set('authorization.roles', [
            'user' => 'DBCO-Gebruiker',
            'user_nationwide' => 'BCO-Landelijk-Gebruiker',
        ]);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);

        $organisation = $this->createOrganisation([
            'external_id' => 'externalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'displayName',
                    'emailAddress' => 'emailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $organisation->external_id,
                        ],
                    ],
                ],
                'roles' => [['name' => 'BCO-Landelijk-Gebruiker']],
            ])),
            new Response(200), // Post to /ggdghornl/oauth2/v1/revoke
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        /** @var AuthManager $authManager */
        $authManager = $this->app->make('auth');

        /** @var EloquentUser|null $user */
        $user = $authManager->guard()->user();
        $this->assertInstanceOf(EloquentUser::class, $user);
        $this->assertEquals('', $user->roles);
    }

    public function testLoginWhenAlreadyAuthorized(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->get('/login');

        $this->isAuthenticated();
        $response->assertRedirect('/');
    }

    #[DataProvider('userRolesDataProvider')]
    public function testLogout(?string $roles): void
    {
        $user = $this->createUser([], $roles);
        $this->be($user);
        $this->assertAuthenticated();

        $response = $this->get('/logout');
        $response->assertRedirect();

        $this->assertGuest();
    }

    public static function userRolesDataProvider(): array
    {
        return [
            'no role' => [null],
            'user' => ['user'],
            'planner' => ['planner'],
            'compliance' => ['compliance'],
            'user & planner' => ['user, planner'],
        ];
    }

    public function testLoginExistingUserSavesNewDetails(): void
    {
        $user = $this->createUser([
            'external_id' => 'identityId',
            'name' => 'oldName',
        ]);

        $authorizationRoleUser = 'DBCO-Gebruiker';
        $testNow = '2020-01-01';
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';

        config()->set('authorization.roles.user', $authorizationRoleUser);
        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);
        CarbonImmutable::setTestNow($testNow);
        CarbonImmutable::setTestNow($testNow);

        $organisation = $this->createOrganisation([
            'external_id' => 'externalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'access_token',
            ])),
            new Response(200, [], json_encode([
                'profile' => [
                    'identityId' => 'identityId',
                    'displayName' => 'newName',
                    'emailAddress' => 'notUsedEmailAddress',
                    'lastLogin' => CarbonImmutable::now()->subMinutes(15)->format('Y-m-d\TH:i:s.v\Z'),
                    'properties' => [
                        $organisationClaim => [
                            $organisation->external_id,
                        ],
                    ],
                ],
                'roles' => [
                    ['name' => $authorizationRoleUser],
                ],
            ])),
            new Response(200), // should not be called
        ]);

        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $this->withSession(['state' => $sessionState])->get(sprintf('/auth/login?state=%s', $sessionState));

        $this->assertDatabaseHas('bcouser', [
            'uuid' => $user->uuid,
            'external_id' => 'identityId',
            'name' => 'newName',
            'last_login_at' => CarbonImmutable::now()->format('Y-m-d H:i:s'),
        ]);
    }
}

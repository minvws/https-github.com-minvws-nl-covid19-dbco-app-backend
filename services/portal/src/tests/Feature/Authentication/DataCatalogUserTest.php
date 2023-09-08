<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use App\Providers\Auth\IdentityHubClient;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use MinVWS\DBCO\Enum\Models\Permission;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;
use function json_encode;
use function route;

class DataCatalogUserTest extends FeatureTestCase
{
    public function testDataCatalogueUserCanVisitDataCatalogue(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], Permission::datacatalog()->value);

        $response = $this->be($user)->get('/catalog');
        $response->assertStatus(200);
    }

    public function testDataCatalogUserGetsRedirectedToCatalogOnLoginWithStubLogin(): void
    {
        $response = $this->get(route('stub-login', ['uuid' => DummySeeder::DEMO_DATACATALOG]));
        $response->assertRedirect(route('catalog'));
    }

    public function testOthersAreNotRedirectedToCatalogWithStubLogin(): void
    {
        $response = $this->get(route('stub-login', ['uuid' => DummySeeder::DEMO_USER_UUID]));
        $response->assertStatus(302);
        $response->assertLocation('/');
    }

    public static function redirectionDataProvider(): array
    {
        return [
            ['/', DummySeeder::DEMO_USER_UUID, '/'],
            ['/', DummySeeder::DEMO_DATACATALOG, 'catalog'],
        ];
    }

    #[DataProvider('redirectionDataProvider')]
    public function testItRedirectsToIntendedForOtherUserWhenIntentSet(string $intent, string $uuid, string $expected): void
    {
        $response = $this->get(route('stub-login', ['uuid' => $uuid]));
        $response->assertRedirect($expected);
    }

    public function testItRedirectsToIntendedForOtherUserWhenNoIntentSet(): void
    {
        $response = $this->get(route('stub-login', ['uuid' => DummySeeder::DEMO_DATACATALOG]));
        $response->assertRedirect('catalog');
    }

    public function testDataCatalogUserCannotVisitCasesPage(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], Permission::datacatalog()->value);

        $response = $this->be($user)->get('/cases');
        $response->assertStatus(403);
    }

    public function testItRedirectsTheUserToTheIntendedRouteWhenIntentExists(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], Permission::datacatalog()->value);

        //Set intent
        $response = $this->get(route('user-profile'));
        //redirect to login
        $response->assertRedirect(route('login'));
        //login
        $response = $this->be($user)->get(route('login'));
        //assert redirect as intended
        $response->assertRedirect(route('user-profile'));
    }

    public function testItDoesNotRedirectToCatalogWhenIntentExists(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], Permission::datacatalog()->value);

        //Set intent
        $response = $this->get(route('user-profile'));
        //redirect to login
        $response->assertRedirect(route('login'));
        //login
        $response = $this->be($user)->get(route('login'));
        //assert redirect as intended
        $this->assertNotEquals(route('catalog'), $response->headers->get('Location'));
    }

    public function testDataCatalogUserCannotVisitTakenPage(): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], Permission::datacatalog()->value);

        $response = $this->be($user)->get('/taken');
        $response->assertForbidden();
    }

    public function testUserWithoutRoleCannotAccessDataCatalogPage(): void
    {
        $user = $this->createUser([], null);
        $response = $this->be($user)->get(route('catalog'));
        $response->assertForbidden();
    }

    public static function realUserProvider(): array
    {
        return [
            [
                'DBCO-Datacatalogus',
                '/catalog',
                [
                    Permission::datacatalog()->value => 'DBCO-Datacatalogus',
                ],
            ],
            [
                'DBCO-Gebruiker',
                '/',
                [
                    'user' => 'DBCO-Gebruiker',
                    Permission::datacatalog()->value => 'DBCO-Datacatalogus',
                ],
            ],
        ];
    }

    #[DataProvider('realUserProvider')]
    public function testCanLoginThroughOAuthIntegrationAndGetRedirectedProperly(
        string $roleToBe,
        string $shouldBeRedirectedTo,
        array $rolesToSet,
    ): void {
        $authorizationRoleUser = $roleToBe;
        $organisationClaim = 'http://schemas.ggd.nl/organisationClaim';

        foreach ($rolesToSet as $role => $roleName) {
            config()->set('authorization.roles.' . $role, $roleName);
        }

        config()->set('session.lifetime', 30);
        config()->set('services.identityhub.claims.vrRegioCode', $organisationClaim);

        $organisation = $this->createOrganisation([
            'external_id' => 'externalOrganisationId',
            'type' => OrganisationType::regionalGGD(),
        ]);

        $mock = $this->getMockHandler($organisationClaim, $organisation, $authorizationRoleUser);
        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);
        $this->instance(IdentityHubClient::class, $client);

        $sessionState = 'foo';
        $response = $this
            ->withSession(['state' => $sessionState])
            ->get(route('provider-login-callback', ['state' => 'foo']));

        $response->assertRedirect($shouldBeRedirectedTo);
        $this->assertAuthenticated();
    }

    public static function factoryUserProvider(): array
    {
        return [
            [
                DummySeeder::DEMO_NOROLE_UUID,
                '',
            ],
            [
                DummySeeder::DEMO_DATACATALOG,
                Permission::datacatalog()->value,
            ],
        ];
    }

    #[DataProvider('factoryUserProvider')]
    public function testCanAccessCatalogPageWithDatacatalogRole(string $uuid, ?string $role): void
    {
        $user = $this->createUser([
            'consented_at' => CarbonImmutable::now()->subDay(),
        ], $role);

        $response = $this->be($user->refresh())->get('/catalog');
        $response->assertOk();
    }

    public function getMockHandler(
        string $organisationClaim,
        EloquentOrganisation $organisation,
        string $authorizationRoleUser,
    ): MockHandler {
        return new MockHandler([
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
    }
}

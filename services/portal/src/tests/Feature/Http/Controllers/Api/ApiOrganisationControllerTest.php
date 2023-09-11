<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Services\OrganisationService;
use Generator;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('organisation')]
final class ApiOrganisationControllerTest extends FeatureTestCase
{
    public OrganisationService $organisationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisationService = app(OrganisationService::class);
    }

    #[DataProvider('availableForOutsourcingProvider')]
    #[DataProvider('updateBcoPhase')]
    public function testUpdateCurrentOrganisation(
        string $userRole,
        bool $hasOutsourceToggle,
        array $payload,
        int $expectedStatusCode,
    ): void {
        $user = $this->createUser(
            [],
            $userRole,
            ['hasOutsourceToggle' => $hasOutsourceToggle, 'name' => 'Test Company'],
        );

        $this->be($user);
        $response = $this->json('PUT', '/api/organisations/current', $payload);
        $this->assertStatus($response, $expectedStatusCode);

        if ($expectedStatusCode !== 200) {
            return;
        }

        $response->assertJsonPath('name', 'Test Company');
        $response->assertJsonPath('hasOutsourceToggle', $hasOutsourceToggle);
        foreach ($payload as $key => $value) {
            $response->assertJsonPath($key, $value);
        }
    }

    public static function updateBcoPhase(): array
    {
        return [
            'Outsource toggle disabled, update BCO phase as planner' =>
                [
                    'planner',
                    false,
                    [
                        'bcoPhase' => BCOPhase::phase2()->value,
                    ],
                    200,
                ],

            'Outsource toggle enabled, update BCO phase as planner' =>
                [
                    'planner',
                    true,
                    [
                        'bcoPhase' => BCOPhase::phase2()->value,
                    ],
                    200,
                ],

            'update BCO phase as user' =>
                [
                    'user',
                    true,
                    [
                        'bcoPhase' => BCOPhase::phase2()->value,
                    ],
                    403,
                ],
        ];
    }

    public static function availableForOutsourcingProvider(): array
    {
        return [
            'Outsource toggle disabled, set unavailable for outsourcing as planner' =>
                [
                    'planner',
                    false,
                    [
                        'isAvailableForOutsourcing' => false,
                    ],
                    200,
                ],
            'Outsource toggle disabled, set available for outsourcing as planner' =>
                [
                    'planner',
                    false,
                    [
                        'isAvailableForOutsourcing' => false,
                    ],
                    200,
                ],
            'Outsource toggle enabled, set unavailable for outsourcing as planner' =>
                [
                    'planner',
                    true,
                    [
                        'isAvailableForOutsourcing' => false,
                    ],
                    200,
                ],
            'Outsource toggle enabled, set available for outsourcing as planner' =>
                [
                    'planner',
                    true,
                    [
                        'isAvailableForOutsourcing' => true,
                    ],
                    200,
                ],
            'Outsource toggle disabled, set unavailable for outsourcing as user' =>
                [
                    'user',
                    false,
                    [
                        'isAvailableForOutsourcing' => false,
                    ],
                    403,
                ],
            'Outsource toggle disabled, set available for outsourcing as user' =>
                [
                    'user',
                    false,
                    [
                        'isAvailableForOutsourcing' => true,
                    ],
                    403,
                ],
            'Outsource toggle enabled, set unavailable for outsourcing as user' =>
                [
                    'user',
                    true,
                    [
                        'isAvailableForOutsourcing' => false,
                    ],
                    403,
                ],
            'Outsource toggle enabled, set available for outsourcing as user' =>
                [
                    'user',
                    true,
                    [
                        'isAvailableForOutsourcing' => true,
                    ],
                    403,
                ],
        ];
    }

    public function testListOrganisationsListsAllOrganisations(): void
    {
        // GIVEN a planner exists
        $planner = $this->createUser([], 'planner');
        $this->be($planner);

        // WHEN planner gets organisations
        $response = $this->json('GET', '/api/organisations');

        // THEN the response is OK
        $this->assertStatus($response, 200);
        // AND the response contains an array of json objects
        $organisations = $response->json();
        $this->assertIsArray($organisations);
        // AND the array contains all 27 regional GGD organisations
        $this->assertCount(27, $organisations);
    }

    /**
     * @param BCOPhase $payloadBcoPhase
     * @param BCOPhase $expectedBcoPhase
     */
    #[DataProvider('updateCurrentOrganisationBcoPhaseDataProvider')]
    public function testUpdateCurrentOrganisationBcoPhase(
        string $userRole,
        BCOPhase $initialBcoPhase,
        string $payloadBcoPhaseValue,
        string $expectedBcoPhaseValue,
        int $expectedStatusCode,
    ): void {
        $this->be($user = $this->createUser([], $userRole, [
            'bco_phase' => $initialBcoPhase,
        ]));

        // Make request with payload bco phase
        $response = $this->patchJson('/api/organisation/current/bcophase', [
            'bco_phase' => $payloadBcoPhaseValue,
        ]);

        // Assert response
        $response->assertStatus($expectedStatusCode);

        // Can only check the response for BCO Phase if it was successful
        if ($response->status() === 200) {
            $response->assertJson(['bcoPhase' => $expectedBcoPhaseValue]);
        }

        // Check the database
        $this->assertDatabaseHas('organisation', [
            'uuid' => $user->organisations->first()->uuid,
            'bco_phase' => $expectedBcoPhaseValue,
        ]);
    }

    public static function updateCurrentOrganisationBcoPhaseDataProvider(): Generator
    {
        yield 'Planner | Accepted change BcoPhase `phase2` from `phase1a`' => [
            'planner',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase2()->value,
            200,
        ];

        yield 'Planner | Declined change BcoPhase that does not exists from `phase1a`' => [
            'planner',
            BCOPhase::phase1(),
            'teapot',
            BCOPhase::phase1()->value,
            422,
        ];

        yield 'User | Forbidden change BcoPhase `phase2` from `phase1a`' => [
            'user',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase1()->value,
            403,
        ];
    }

    public function testExistingCaseWillNotGetHisBcoPhaseUpdatedWhenOrganisationBcoPhaseUpdates(): void
    {
        // create organisation
        $organisation = $this->createOrganisation([
            'bco_phase' => BCOPhase::phase1(),
        ]);

        // create users
        $user = $this->createUserForOrganisation($organisation);
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // Be the planner
        $this->be($planner);

        // Create a case with the initial bco phase
        $case = $this->createCaseForUser($user);

        // Update bco_phase through service
        $this->organisationService->updateOrganisationBcoPhase($organisation, BCOPhase::phase2());

        // assert that the covid case has the correct bco phase
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'bco_phase' => BCOPhase::phase1(),
        ]);

        // assert that the covid organisation has the correct bco phase
        $this->assertDatabaseHas('organisation', [
            'uuid' => $organisation->uuid,
            'bco_phase' => BCOPhase::phase2(),
        ]);
    }
}

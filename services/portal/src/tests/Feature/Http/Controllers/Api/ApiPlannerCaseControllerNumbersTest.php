<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\General;
use App\Models\CovidCase\Test;
use App\Models\OrganisationType;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_merge;
use function collect;
use function config;
use function sprintf;

#[Group('planner-case')]
class ApiPlannerCaseControllerNumbersTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2020-01-01');
    }

    #[DataProvider('plannerBcoNumberDataProvider')]
    public function testCreatePlannerCaseBcoNumber(
        array $payload,
        bool $expectBcoNumberCreated,
    ): void {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);
        $response->assertStatus(201);
        $bcoNumber = $response->json('data')['general']['reference'];

        if ($expectBcoNumberCreated) {
            $this->assertDatabaseHas(
                'bco_numbers',
                [
                    'bco_number' => $bcoNumber,
                ],
            );
        } else {
            $this->assertDatabaseMissing(
                'bco_numbers',
                [
                    'bco_number' => $bcoNumber,
                ],
            );
        }
    }

    public static function plannerBcoNumberDataProvider(): array
    {
        $minimalValidPayload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ];

        return [
            'make bco_number when hpzone is not supplied' => [
                array_merge($minimalValidPayload, [
                    'test' => [
                        'monsterNumber' => '123A456789',
                    ],
                ]),
                true,
            ],
        ];
    }

    #[DataProvider('validPlannerCaseUpdateDataProvider')]
    public function testUpdatePlannerCase(array $payload, array $expectedCovidCaseData): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::draft(),
                'date_of_test' => null,
            ],
        );

        $case->general->hpzoneNumber = '1111111';
        $case->test->monsterNumber = '123A4567';
        $case->save();

        $this->assertDatabaseMissing('covidcase', $expectedCovidCaseData);

        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s', $case->uuid), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('covidcase', $expectedCovidCaseData);
    }

    public static function validPlannerCaseUpdateDataProvider(): array
    {
        return [
            'update hpzone nr' => [
                [
                    'general' => [
                        'hpzoneNumber' => '1234568',
                    ],
                ],
                [
                    'hpzone_number' => '1234568',
                    'test_monster_number' => '123A4567',
                ],
            ],
            'update monsterNumber nr' => [
                [
                    'general' => [
                        'hpzoneNumber' => '1111111',
                    ],
                    'test' => [
                        'monsterNumber' => '123A4568',
                    ],
                ],
                [
                    'test_monster_number' => '123A4568',
                    'hpzone_number' => '1111111',
                ],
            ],
        ];
    }

    #[DataProvider('updateCaseErrorsDataProvider')]
    public function testUpdateCaseErrors(array $payload, array $expectedErrors): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::draft(),
                'date_of_test' => null,
            ],
        );

        $case->general->hpzoneNumber = '1111111';
        $case->test->monsterNumber = '123A4567';
        $case->save();


        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s', $case->uuid), $payload);

        if (empty($expectedErrors)) {
            $response->assertStatus(200);
            return;
        }

        $response->assertStatus(422);
        $validationResult = $response->json()['validationResult'];

        foreach ($expectedErrors as $expectedMessage) {
            $this->assertContains($expectedMessage, collect($validationResult)->flatten()->all());
        }
    }

    public static function updateCaseErrorsDataProvider(): array
    {
        return [
            'clearing only hpzoneNumber is allowed' => [
                [
                    'general' => [
                        'hpzoneNumber' => null,
                    ],
                    'test' => [
                        'monsterNumber' => '123A4567',
                    ],
                ],
                [],
            ],
            'clearing only monsterNumber is allowed' => [
                [
                    'general' => [
                        'hpzoneNumber' => '1231232',
                    ],
                    'test' => [
                        'monsterNumber' => null,
                    ],
                ],
                [],
            ],
            'clearing both hpzoneNumber & monsterNumber is allowed' => [
                [
                    'general' => [
                        'hpzoneNumber' => null,
                    ],
                    'test' => [
                        'monsterNumber' => null,
                    ],
                ],
                [],
            ],
        ];
    }

    #[DataProvider('invalidPlannerCaseDataProvider')]
    public function testCreateCaseValidation(array $payload, array $expectedErrors): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(422);
        $validationResult = $response->json()['validationResult'];

        foreach ($expectedErrors as $expectedMessage) {
            $this->assertContains($expectedMessage, collect($validationResult)->flatten()->all());
        }
    }

    public static function invalidPlannerCaseDataProvider(): array
    {
        $payload = collect([
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ]);

        return [
            'hpzone nr too long' => [
                $payload->merge(['general' => ['hpzoneNumber' => '123456789']])->all(),
                ['Veld "HPZone-nummer" moet uit 7 of 8 cijfers bestaan.'],
            ],
            'hpzone nr too short' => [
                $payload->merge(['general' => ['hpzoneNumber' => '123456']])->all(),
                ['Veld "HPZone-nummer" moet uit 7 of 8 cijfers bestaan.'],
            ],
        ];
    }

    #[DataProvider('uniquenessErrorsPlannerCaseDataProvider')]
    public function testUniquenessErrors(array $payload, array $expectedErrors): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForUser($user, ['case_id' => '345-345-345']);
        $case->general = new General();
        $case->general->hpzoneNumber = '1234567';
        $case->test = new Test();
        $case->test->monsterNumber = '123A4567';
        $case->save();

        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(422);
        $validationResult = $response->json()['validationResult'];

        foreach ($expectedErrors as $expectedMessage) {
            $this->assertContains($expectedMessage, collect($validationResult)->flatten()->all());
        }
    }

    public static function uniquenessErrorsPlannerCaseDataProvider(): array
    {
            return [
                'duplicate hpzone nr' => [
                    [
                        'index' => [
                            'firstname' => 'foo',
                            'lastname' => 'bar',
                            'dateOfBirth' => '1950-01-01',
                        ],
                        'contact' => ['phone' => '06 12345678'],
                        'test' => ['dateOfTest' => null],
                        'general' => ['hpzoneNumber' => '1234567'],
                    ],
                    ['Er bestaat al een case met dit HPZone nummer'],
                ],
                'duplicate monsterNumber nr' => [
                    [
                        'index' => [
                            'firstname' => 'foo',
                            'lastname' => 'bar',
                            'dateOfBirth' => '1950-01-01',
                        ],
                        'contact' => ['phone' => '06 12345678'],
                        'test' => ['monsterNumber' => '123A4567'],
                    ],
                    ['Er bestaat al een case met dit monster nummer'],
                ],
            ];
    }

    public function test400StatusIsReturnedIfBcoNumberCannotBeGenerated(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);

        $payload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ];

        config()->set('misc.bcoNumbers.maxRetries', 0);

        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);
        $response->assertStatus(400);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseFragmentService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\SchemaVersionDataProvider;
use Tests\Feature\Http\Controllers\ControllerTestCase;

use function sprintf;

#[Group('case')]
class ApiCaseValidationStatusControllerTest extends ControllerTestCase
{
    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusEndpoint(int $schemaVersion): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'schema_version' => $schemaVersion,
        ]);

        $this->mock(CaseFragmentService::class, static function (MockInterface $mock) use ($case): void {
            $mock->expects('validateAllFragments')
                ->with(
                    Mockery::on(static function (EloquentCase $passedCase) use ($case) {
                        return $passedCase->uuid === $case->uuid;
                    }),
                    [],
                )
                ->andReturn([]);
        });

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/validation-status', $case->uuid));
        $response->assertStatus(200);
        $this->assertAuditEventForCase($case);
    }

    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusEndpointWithFilter(int $schemaVersion): void
    {
        $filter = $this->faker->word();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'schema_version' => $schemaVersion,
        ]);

        $this->mock(CaseFragmentService::class, static function (MockInterface $mock) use ($case, $filter): void {
            $mock->expects('validateAllFragments')
                ->with(
                    Mockery::on(static function (EloquentCase $passedCase) use ($case) {
                        return $passedCase->uuid === $case->uuid;
                    }),
                    [$filter],
                )
                ->andReturn([]);
        });

        $response = $this->be($user)->getJson(
            sprintf('/api/cases/%s/validation-status?filter=%s', $case->uuid, $filter),
        );
        $response->assertStatus(200);
    }

    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusWithoutValidationErrors(int $schemaVersion): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $case = $this->createCaseForUser($user, [
            'schema_version' => $schemaVersion,
            'index' => $this->createFragment(Index::class),
        ]);

        $response = $this->be($user)->getJson(
            sprintf('/api/cases/%s/validation-status?filter=tag_osiris_final', $case->uuid),
        );
        $response->assertStatus(200);

        $expectedResultJson = [
            'validationResult' => [],
        ];
        $response->assertExactJson($expectedResultJson);
    }

    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusWithValidationErrors(int $schemaVersion): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $case = $this->createCaseForUser($user, [
            'schema_version' => $schemaVersion,
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/validation-status', $case->uuid));
        $response->assertStatus(200);

        $expectedResultJson = [
            'validationResult' => [
                'index' => [
                    'fatal' => [
                        'errors' => [
                            'firstname' => ['Veld "Voornaam" is verplicht.'],
                            'lastname' => ['Veld "Achternaam" is verplicht.'],
                        ],
                        'failed' => [
                            'firstname' => [
                                'Required' => [],
                            ],
                            'lastname' => [
                                'Required' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response->assertExactJson($expectedResultJson);
    }

    public static function caseVersionDataProvider(): array
    {
        return SchemaVersionDataProvider::all(EloquentCase::class);
    }
}

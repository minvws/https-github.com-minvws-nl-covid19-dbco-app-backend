<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseFragmentService;
use Carbon\CarbonImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\SchemaVersionDataProvider;
use Tests\Feature\Http\Controllers\ControllerTestCase;

use function sprintf;

#[Group('osiris-validation')]
class ApiCaseValidationStatusMessagesControllerTest extends ControllerTestCase
{
    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusMessagesEndpoint(int $schemaVersion): void
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
                    false,
                )
                ->andReturn([]);
        });

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/validation-status/messages', $case->uuid));

        $response->assertStatus(200);
        $this->assertAuditEventForCase($case);
    }

    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusMessagesEndpointWithFilter(int $schemaVersion): void
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
                    false,
                )
                ->andReturn([]);
        });

        $response = $this->be($user)->getJson(
            sprintf('/api/cases/%s/validation-status/messages?filter=%s', $case->uuid, $filter),
        );
        $response->assertStatus(200);
    }

    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusMessagesWithoutValidationErrors(int $schemaVersion): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $case = $this->createCaseForUser($user, [
            'schema_version' => $schemaVersion,
            'index' => $this->createFragment(Index::class),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/validation-status/messages', $case->uuid));
        $response->assertStatus(200);

        $expectedResultJson = [
            'fatal' => [],
            'warning' => [],
            'notice' => [],
        ];
        $response->assertExactJson($expectedResultJson);
    }

    #[DataProvider('caseVersionDataProvider')]
    public function testGetCaseValidationStatusMessagesWithValidationErrors(int $schemaVersion): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $case = $this->createCaseForUser($user, [
            'schema_version' => $schemaVersion,
            'index' => Index::newInstanceWithVersion(1, static function (Index $index): void {
                $index->dateOfBirth = CarbonImmutable::now()->subYears(127);
            }),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/validation-status/messages', $case->uuid));
        $response->assertStatus(200);

        $expectedResultJson = [
            'fatal' => [
                'Veld "Voornaam" is verplicht.',
                'Veld "Achternaam" is verplicht.',
            ],
            'warning' => [
                'Veld "Geboortedatum" mag niet voor deze datum liggen: 1906-01-01.',
            ],
            'notice' => [],
        ];
        $response->assertExactJson($expectedResultJson);
    }

    public static function caseVersionDataProvider(): array
    {
        return SchemaVersionDataProvider::all(EloquentCase::class);
    }
}

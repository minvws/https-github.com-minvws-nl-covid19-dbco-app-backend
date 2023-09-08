<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Case;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Moment;
use App\Models\Export\ExportClient;
use App\Models\Purpose\Purpose;
use App\Models\Task\Circumstances;
use App\Models\Task\General;
use App\Models\Task\Symptoms;
use App\Models\Task\Test;
use App\Models\Versions\Task\Test\TestV2;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Testing\TestResponse;
use MinVWS\DBCO\Enum\Models\PersonalProtectiveEquipment;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\Feature\FeatureTestCase;

use function array_keys;
use function route;

#[Group('export')]
#[Group('export-case')]
class ApiExportCaseControllerIndexSingleCaseTest extends FeatureTestCase
{
    private EloquentOrganisation $organisation;
    private ExportClient $client;
    private EloquentCase $case;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getCaseFromApiShowRequest(EloquentCase $case): TestResponse
    {
        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $pseudoId = $pseudoIdHelper->idToPseudoIdForClient($case->uuid, $this->client);
        return $this->be($this->client, 'export')->get(route('api-export-case', ['pseudoId' => $pseudoId], false));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = $this->createOrganisation();

        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$this->organisation],
        );

        $stamp = CarbonImmutable::parse('30 minutes ago');
        $this->case = $this->createCaseForOrganisation($this->organisation, ['created_at' => $stamp, 'updated_at' => $stamp]);
    }

    public function testReturnsFixedSetOfDataForNonDeletedCase(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $response->assertStatus(200);
        $item = $response->json('items.0');
        $this->assertEqualsCanonicalizing(['pseudoId', 'path', 'mutatedAt'], array_keys($item));
        $this->assertEquals(route('api-export-case', ['pseudoId' => $item['pseudoId']], false), $item['path']);
    }

    public function testReturnsFixedSetOfDataForDeletedCase(): void
    {
        $this->travel(-20)->minutes();
        $this->case->delete();
        $this->travelBack();

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $response->assertStatus(200);
        $item = $response->json('items.0');
        $this->assertEqualsCanonicalizing(['pseudoId', 'deletedAt'], array_keys($item));
    }

    public function testPseudoIdIsForClient(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $response->assertStatus(200);
        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $uuid = $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.0.pseudoId'), $this->client);
        $this->assertEquals($this->case->uuid, $uuid);
    }

    public static function transactionWindowProvider(): Generator
    {
        yield [0, 0]; // now
        yield [-5 * 60, 0]; // -00:05:00
        yield [-10 * 60, 0]; // -00:10:00
        yield [-15 * 60 + 1, 0]; // -00:14:59
        yield [-15 * 60, 0]; // -00:15:00
        yield [-15 * 60 - 1, 1]; // -00:15:01
        yield [-16 * 60, 1]; // -00:16:00
        yield [-20 * 60, 1]; // -00:20:00
    }

    #[DataProvider('transactionWindowProvider')]
    #[Group('yy')]
    public function testCaseShouldBeModifiedOutsideTransactionWindowToBeIncluded(int $travelSeconds, int $expectedCount): void
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now->clone()->addSeconds($travelSeconds));

        $this->case->touch();
        $this->case->save();

        CarbonImmutable::setTestNow($now);

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $response->assertStatus(200);
        $this->assertCount($expectedCount, $response->json('items'));
    }

    public function testMomentsSourceAndFormattedAreAlwaysNull(): void
    {
        $case = EloquentCase::factory()
            ->withFragments()
            ->has(Context::factory()->has(Moment::factory()))
            ->create([
                'organisation_uuid' => $this->organisation->uuid,
            ]);

        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $pseudoId = $pseudoIdHelper->idToPseudoIdForClient($case->uuid, $this->client);
        $caseData = $this->be($this->client, 'export')->get(route('api-export-case', ['pseudoId' => $pseudoId], false));
        $moments = $caseData->json('contexts.0.general.moments');
        foreach ($moments as $moment) {
            $this->assertNull($moment['source']);
            $this->assertNull($moment['formatted']);
        }
    }

    public function testOtherRelationShipIsExportedCorrectly(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);
        $expectedRelationship = $this->faker->word();
        $this->createTaskForCase($case, [
            'general' => General::newInstanceWithVersion(
                1,
                static fn(General $general) => $general->otherRelationship = $expectedRelationship,
            ),
        ]);

        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $pseudoId = $pseudoIdHelper->idToPseudoIdForClient($case->uuid, $this->client);
        $caseData = $this->be($this->client, 'export')->get(route('api-export-case', ['pseudoId' => $pseudoId], false));
        $otherRelationship = $caseData->json('tasks.0.general.otherRelationship');
        $this->assertEquals($expectedRelationship, $otherRelationship);
    }

    public function testTestResultIsExported(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);
        $expectedResult = $this->faker->randomElement(TestResult::all());
        $expectedDate = $this->faker->dateTimeBetween('2021-01-01');
        $reported = $this->faker->randomElement(YesNoUnknown::all());
        $expectedPreviousInfectionDate = $this->faker->dateTimeBetween('2020-01-01', $expectedDate);
        $this->createTaskForCase($case, [
            'test' => Test::newInstanceWithVersion(2, static function (TestV2 $test) use (
                $expectedResult,
                $expectedDate,
                $reported,
                $expectedPreviousInfectionDate,
            ): void {
                $test->testResult = $expectedResult;
                $test->dateOfTest = $expectedDate;
                $test->previousInfectionReported = $reported;
                $test->previousInfectionDateOfSymptom = $expectedPreviousInfectionDate;
            }),
        ]);

        $result = $this->getCaseFromApiShowRequest($case);
        $test = $result->json('tasks.0.test');
        $this->assertEquals($expectedResult, $test['testResult']);
        $this->assertEquals($expectedDate->format('Y-m-d'), $test['dateOfTest']);
        $this->assertEquals($reported, $test['previousInfectionReported']);
        $this->assertEquals($expectedPreviousInfectionDate->format('Y-m-d'), $test['previousInfectionDateOfSymptom']);
    }

    public function testSymptomsIsExported(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);
        $otherSymptom = $this->faker->word();
        $this->createTaskForCase($case, [
            'symptoms' => Symptoms::newInstanceWithVersion(1, static function (Symptoms $symptoms) use ($otherSymptom): void {
                $symptoms->otherSymptoms = [$otherSymptom];
            }),
        ]);

        $response = $this->getCaseFromApiShowRequest($case);
        $symptoms = $response->json('tasks.0.symptoms');
        $this->assertEquals([$otherSymptom], $symptoms['otherSymptoms']);
    }

    public function testCircumstancesIsExportedCorrectly(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $used = $this->faker->randomElement(PersonalProtectiveEquipment::all());
        $type = $this->faker->word();
        $replaceFrequency = $this->faker->word();

        $this->createTaskForCase($case, [
            'circumstances' => Circumstances::newInstanceWithVersion(
                1,
                static function (Circumstances $circumstances) use ($used, $type, $replaceFrequency): void {
                    $circumstances->usedPersonalProtectiveEquipment = [$used];
                    $circumstances->ppeType = $type;
                    $circumstances->ppeReplaceFrequency = $replaceFrequency;
                },
            ),
        ]);

        $response = $this->getCaseFromApiShowRequest($case);
        $circumstances = $response->json('tasks.0.circumstances');
        $this->assertEquals([$used], $circumstances['usedPersonalProtectiveEquipment']);
        $this->assertEquals($type, $circumstances['ppeType']);
        $this->assertEquals($replaceFrequency, $circumstances['ppeReplaceFrequency']);
    }
}

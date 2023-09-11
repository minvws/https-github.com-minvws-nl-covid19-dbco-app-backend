<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Dto\TestResultReport\TypeOfTest;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Services\TestResult\TestResultImportService;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

class TestResultImportServiceTest extends FeatureTestCase
{
    public function testImportDatabaseSaves(): void
    {
        /** @var TypeOfTest $typeOfTest */
        $typeOfTest = $this->faker->randomElement(TypeOfTest::cases());
        $testResultTypeOfTest = TypeOfTest::toTestResultTypeOfTest($typeOfTest);

        $pseudoBsnGuid = $this->faker->uuid();

        $testResultReportData = TestResultDataProvider::payload();
        $testResultReportData['test']['typeOfTest'] = $typeOfTest->value;

        $testResultReport = TestResultReport::fromArray($testResultReportData);
        $organisation = $this->createOrganisation();
        $pseudoBsn = new PseudoBsn($pseudoBsnGuid, $this->faker->uuid(), $this->faker->randomLetter);

        $testResultImportService = $this->app->get(TestResultImportService::class);
        $testResult = $testResultImportService->import($testResultReport, $organisation, $pseudoBsn);

        // Make sure the type of test is cast correctly
        $this->assertEquals($testResultTypeOfTest, $testResult->type_of_test);

        $this->assertDatabaseHas('person', [
            'pseudo_bsn_guid' => $pseudoBsnGuid,
        ]);
        $this->assertDatabaseHas('test_result', [
            'id' => $testResult->id,
            'organisation_uuid' => $organisation->uuid,
            'type' => 'lab',
            'message_id' => $testResultReportData['messageId'],
            'monster_number' => $testResultReportData['test']['sampleId'],
            'type_of_test' => $testResultTypeOfTest->value,
            'date_of_result' => '2021-02-01 16:30:50',
            'result' => TestResultResult::positive()->value,
        ]);
        $this->assertDatabaseHas('test_result_raw', [
            'test_result_id' => $testResult->id,
        ]);
    }

    public function testInitialsStoredInDatabase(): void
    {
        $initials = $this->faker->name();

        $payload = TestResultDataProvider::payload();
        $payload['person']['initials'] = $initials;

        $testResultReport = TestResultReport::fromArray($payload);

        $testResultImportService = $this->app->get(TestResultImportService::class);
        $testResult = $testResultImportService->import(
            $testResultReport,
            $this->createOrganisation(),
            new PseudoBsn('', '', ''),
        );

        $testResult->refresh();
        $this->assertEquals($initials, $testResult->person->nameAndAddress->initials);
    }
}

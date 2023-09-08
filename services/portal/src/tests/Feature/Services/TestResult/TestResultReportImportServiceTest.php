<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Exceptions\SkipTestResultImportException;
use App\Exceptions\TestReportingNotAllowedForOrganisationException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Services\TestResult\CaseImportService;
use App\Services\TestResult\IdentificationService;
use App\Services\TestResult\TestResultAssignmentService;
use App\Services\TestResult\TestResultReportImportService;
use Mockery\MockInterface;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

class TestResultReportImportServiceTest extends FeatureTestCase
{
    public function testImportAlreadyProcessed(): void
    {
        $payload = TestResultDataProvider::payload();
        $testResultReport = TestResultReport::fromArray($payload);

        $this->createTestResult([
            'message_id' => $payload['messageId'],
        ]);

        $testResultReportImportService = $this->app->get(TestResultReportImportService::class);

        $this->expectException(SkipTestResultImportException::class);
        $testResultReportImportService->import($testResultReport);
    }

    public function testImportOrganisationNotAllowedToReportTestResults(): void
    {
        $organisationHpZoneCode = $this->faker->uuid();

        $this->createOrganisation([
            'hp_zone_code' => $organisationHpZoneCode,
            'is_allowed_to_report_test_results' => false,
        ]);

        $payload = TestResultDataProvider::payload();
        $payload['ggdIdentifier'] = $organisationHpZoneCode;
        $testResultReport = TestResultReport::fromArray($payload);

        $testResultReportImportService = $this->app->get(TestResultReportImportService::class);

        $this->expectException(TestReportingNotAllowedForOrganisationException::class);
        $testResultReportImportService->import($testResultReport);
    }

    public function testImportUnidentifiedCase(): void
    {
        $payload = TestResultDataProvider::payload();
        $testResultReport = TestResultReport::fromArray($payload);

        $this->createOrganisation([
            'hp_zone_code' => $payload['ggdIdentifier'],
            'is_allowed_to_report_test_results' => true,
        ]);

        $this->mock(IdentificationService::class, static function (MockInterface $mock): void {
            $mock->expects('identify')
                ->andReturn(null);
        });

        $this->mock(CaseImportService::class, static function (MockInterface $mock): void {
            $mock->expects('importUnidentifiedCase');
        });

        $testResultReportImportService = $this->app->get(TestResultReportImportService::class);
        $testResultReportImportService->import($testResultReport);
    }

    public function testImportOnExistingCase(): void
    {
        $payload = TestResultDataProvider::payload();
        $testResultReport = TestResultReport::fromArray($payload);

        $organisation = $this->createOrganisation([
            'hp_zone_code' => $payload['ggdIdentifier'],
            'is_allowed_to_report_test_results' => true,
        ]);
        $case = $this->createCaseForOrganisation($organisation);

        $this->mock(IdentificationService::class, function (MockInterface $mock): void {
            $mock->expects('identify')
                ->andReturn(new PseudoBsn($this->faker->uuid(), $this->faker->uuid(), $this->faker->randomLetter));
        });

        $this->mock(TestResultAssignmentService::class, static function (MockInterface $mock) use ($case): void {
            $mock->expects('findCaseForAssignment')->andReturn($case);
            $mock->expects('assignTestResultToCase');
            $mock->expects('addRepeatResultLabel');
        });

        $testResultReportImportService = $this->app->get(TestResultReportImportService::class);
        $testResultReportImportService->import($testResultReport);
    }

    public function testImportForNewIdentifiedCase(): void
    {
        $payload = TestResultDataProvider::payload();
        $testResultReport = TestResultReport::fromArray($payload);

        $this->createOrganisation([
            'hp_zone_code' => $payload['ggdIdentifier'],
            'is_allowed_to_report_test_results' => true,
        ]);

        $this->mock(IdentificationService::class, function (MockInterface $mock): void {
            $mock->expects('identify')
                ->andReturn(new PseudoBsn($this->faker->uuid(), $this->faker->uuid(), $this->faker->randomLetter));
        });

        $this->mock(TestResultAssignmentService::class, static function (MockInterface $mock): void {
            $mock->expects('findCaseForAssignment')
                ->andReturn(null);
        });

        $this->mock(CaseImportService::class, static function (MockInterface $mock): void {
            $mock->expects('importIdentifiedCase');
        });

        $testResultReportImportService = $this->app->get(TestResultReportImportService::class);
        $testResultReportImportService->import($testResultReport);
    }
}

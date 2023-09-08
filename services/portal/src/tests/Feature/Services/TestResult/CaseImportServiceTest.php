<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentCase;
use App\Models\Metric\Osiris\CaseCreationToForwardingDuration;
use App\Models\Metric\TestResult\CaseCreated;
use App\Models\Metric\TestResult\TestResultToCovidCaseAssignment;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\CaseLabelRepository;
use App\Repositories\Metric\MetricRepository;
use App\Services\TestResult\CaseImportService;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use JsonException;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

final class CaseImportServiceTest extends FeatureTestCase
{
    private CaseImportService $caseImportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseImportService = $this->app->get(CaseImportService::class);
    }

    /**
     * @throws JsonException
     */
    public function testImportUnidentifiedCase(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();

        $this->caseImportService->importUnidentifiedCase($testResultReport, $testResult);

        $testResult->refresh();
        $this->assertInstanceOf(EloquentCase::class, $testResult->covidCase);
        $this->assertEquals($testResultReport->orderId, $testResult->covidCase->test_monster_number);

        /** @var CaseLabel $caseLabel */
        $caseLabel = CaseLabel::query()
            ->where('code', CaseLabelRepository::CASE_LABEL_CODE_NOT_IDENTIFIED)
            ->firstOrFail();
        $this->assertDatabaseHas(
            'case_case_label',
            [
                'case_uuid' => $testResult->covidCase->uuid,
                'case_label_uuid' => $caseLabel->uuid,
            ],
        );
    }

    /**
     * @throws JsonException
     */
    public function testImportIdentifiedCase(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();
        $pseudoBsn = new PseudoBsn($this->faker->uuid(), $this->faker->uuid(), $this->faker->randomLetter);

        $this->caseImportService->importIdentifiedCase($testResultReport, $testResult, $pseudoBsn);

        $testResult->refresh();
        $this->assertInstanceOf(EloquentCase::class, $testResult->covidCase);
        $this->assertEquals($testResultReport->orderId, $testResult->covidCase->test_monster_number);
    }

    /**
     * @throws JsonException
     */
    public function testHandleCaseCreatedCounterMetricWithNotIdentifiedStatus(): void
    {
        Event::fake([
            JobProcessed::class,
        ]);
        Queue::fake();

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureHistogram')
                ->with(Mockery::on(static function (CaseCreationToForwardingDuration $metric): bool {
                    return $metric->getLabels() === [];
                }));
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (TestResultToCovidCaseAssignment $metric): bool {
                    return $metric->getLabels() === ['status' => 'new case'];
                }));
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (CaseCreated $metric): bool {
                    return $metric->getLabels() === ['status' => 'not identified'];
                }));
        });

        /** @var CaseImportService $caseImportService */
        $caseImportService = $this->app->get(CaseImportService::class);
        $caseImportService->importUnidentifiedCase($testResultReport, $testResult);
    }

    /**
     * @throws JsonException
     */
    public function testHandleCaseCreatedCounterMetricWithIdentifiedStatus(): void
    {
        Event::fake([
            JobProcessed::class,
        ]);
        Queue::fake();

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();
        $pseudoBsn = new PseudoBsn($this->faker->uuid(), $this->faker->uuid(), $this->faker->randomLetter);

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureHistogram')
                ->with(Mockery::on(static function (CaseCreationToForwardingDuration $metric): bool {
                    return $metric->getLabels() === [];
                }));
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (TestResultToCovidCaseAssignment $metric): bool {
                    return $metric->getLabels() === ['status' => 'new case'];
                }));
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (CaseCreated $metric): bool {
                    return $metric->getLabels() === ['status' => 'identified'];
                }));
        });

        /** @var CaseImportService $caseImportService */
        $caseImportService = $this->app->get(CaseImportService::class);
        $caseImportService->importIdentifiedCase($testResultReport, $testResult, $pseudoBsn);
    }

    /**
     * @throws JsonException
     */
    #[Group('osiris-case-export')]
    public function testImportCaseShouldDispatchOsirisJob(): void
    {
        ConfigHelper::set('misc.test_result.simulation_mode_enabled', false);
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        Queue::fake();

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();

        $this->caseImportService->importUnidentifiedCase($testResultReport, $testResult);

        Queue::assertPushed(ExportCaseToOsiris::class);

        ConfigHelper::disableFeatureFlag('osiris_send_case_enabled');
    }

    /**
     * @throws JsonException
     */
    public function testInitialsStoredInDatabase(): void
    {
        $initials = $this->faker->name();

        $payload = TestResultDataProvider::payload();
        $payload['person']['initials'] = $initials;

        $testResultReport = TestResultReport::fromArray($payload);
        $testResult = $this->createTestResult();

        $case = $this->caseImportService->importUnidentifiedCase($testResultReport, $testResult);

        $case->refresh();
        $this->assertEquals($initials, $case->index->initials);
    }

    /**
     * @throws JsonException
     */
    public function testImportIdentifiedCaseSetsAutomaticAddressVerificationToUnverified(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();
        $pseudoBsn = new PseudoBsn($this->faker->uuid(), $this->faker->numerify('******###'), $this->faker->randomLetter());

        $case = $this->caseImportService->importIdentifiedCase($testResultReport, $testResult, $pseudoBsn);
        $case->refresh();

        $this->assertEquals(AutomaticAddressVerificationStatus::unverified(), $case->automatic_address_verification_status);
    }

    /**
     * @throws JsonException
     */
    public function testImportUnidentifiedCaseSetsAutomaticAddressVerificationToUnverified(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();

        $case = $this->caseImportService->importUnidentifiedCase($testResultReport, $testResult);
        $case->refresh();

        $this->assertEquals(AutomaticAddressVerificationStatus::unverified(), $case->automatic_address_verification_status);
    }

    public function testItCreatesACaseWithNewContactTracingStatus(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResult = $this->createTestResult();

        $case = $this->caseImportService->importUnidentifiedCase($testResultReport, $testResult);
        $case->refresh();

        $this->assertEquals(ContactTracingStatus::new(), $case->status_index_contact_tracing);
    }
}

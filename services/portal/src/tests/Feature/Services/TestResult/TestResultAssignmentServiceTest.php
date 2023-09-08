<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Models\Metric\TestResult\TestResultToCovidCaseAssignment;
use App\Repositories\CaseLabelRepository;
use App\Repositories\Metric\MetricRepository;
use App\Services\TestResult\TestResultAssignmentService;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Mockery;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

use function array_key_exists;

class TestResultAssignmentServiceTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake([
            JobProcessed::class,
        ]);
    }

    public function testAssignTestResultToCase(): void
    {
        $testResult = $this->createTestResult();

        $case = $this->createCase();
        $case->wasRecentlyCreated = false;
        $case->save();

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (TestResultToCovidCaseAssignment $metric): bool {
                    return array_key_exists('status', $metric->getLabels()) &&
                        $metric->getLabels()['status'] === 'existing case';
                }));
        });

        $testResultAssignmentService = $this->app->get(TestResultAssignmentService::class);
        $testResultAssignmentService->assignTestResultToCase($testResult, $case);

        $case->refresh();

        $this->assertEquals($case->bcoStatus, BCOStatus::draft());
        $this->assertTrue($case->caseLabels->doesntContain('code', CaseLabelRepository::CASE_LABEL_REPEAT_RESULT));
    }

    public function testAssignTestResultToCaseForExistingCase(): void
    {
        $testResult = $this->createTestResult();
        $case = $this->createCase();
        $case = $case->fresh();

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (TestResultToCovidCaseAssignment $metric): bool {
                    return array_key_exists('status', $metric->getLabels()) &&
                        $metric->getLabels()['status'] === 'existing case';
                }));
        });

        $testResultAssignmentService = $this->app->get(TestResultAssignmentService::class);
        $testResultAssignmentService->assignTestResultToCase($testResult, $case);

        $this->assertEquals($case->bcoStatus, BCOStatus::draft());
    }

    public function testAssignTestResultToCaseResetsBcoStatus(): void
    {
        $testResult = $this->createTestResult();
        $case = $this->createCase(['bco_status' => BCOStatus::archived()]);

        $testResultAssignmentService = $this->app->get(TestResultAssignmentService::class);
        $testResultAssignmentService->assignTestResultToCase($testResult, $case);

        $case->refresh();

        $this->assertEquals($case->bcoStatus, BCOStatus::draft());
    }

    public function testAssignMultipleTestResultsToCase(): void
    {
        $case = $this->createCase();

        $testResultAssignmentService = $this->app->get(TestResultAssignmentService::class);
        $testResultAssignmentService->assignTestResultToCase($this->createTestResult(), $case);
        $testResultAssignmentService->assignTestResultToCase($this->createTestResult(), $case);
        $testResultAssignmentService->assignTestResultToCase($this->createTestResult(), $case);

        $case->refresh();

        $this->assertEquals(3, $case->testResults()->count());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Exceptions\SkipTestResultImportException;
use App\Exceptions\TestReportingNotAllowedForOrganisationException;
use App\Models\Metric\TestResult\ImportDuration;
use App\Models\Metric\TestResult\ImportStatus;
use App\Models\Metric\TestResult\ProcessingDuration;
use App\Models\Metric\TestResult\ReportingNotAllowedForOrganisation;
use App\Repositories\Metric\MetricRepository;
use App\Services\MessageQueue\Exceptions\UnrecoverableMessageException;
use App\Services\MetricService;
use App\Services\TestResult\ProcessingDurationCalculator;
use App\Services\TestResult\TestResultReportImportService;
use App\Services\TestResult\TestResultReportImportServiceMetricDecorator;
use Mockery;
use Mockery\MockInterface;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

use function is_float;

final class TestResultReportImportServiceMetricDecoratorTest extends FeatureTestCase
{
    public function testHandleCountersWhenImportIsSkipped(): void
    {
        $exception = SkipTestResultImportException::messageAlreadyProcessed();

        $decorated = $this->createMock(TestResultReportImportService::class);
        $decorated->expects($this->once())->method('import')
            ->willThrowException($exception);

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (ImportStatus $metric): bool {
                    return $metric->getLabels() === ['status' => 'processed'];
                }));
        });

        $this->expectExceptionObject($exception);

        $metricDecorator = $this->createMetricDecorator($decorated);
        $metricDecorator->import($testResultReport);
    }

    public function testHandleCountersOnUnrecoverableError(): void
    {
        $exception = new UnrecoverableMessageException($this->faker->word());

        $decorated = $this->createMock(TestResultReportImportService::class);
        $decorated->expects($this->once())->method('import')
            ->willThrowException($exception);

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (ImportStatus $metric): bool {
                    return $metric->getLabels() === ['status' => 'failed'];
                }));
        });

        $this->expectExceptionObject($exception);

        $metricDecorator = $this->createMetricDecorator($decorated);
        $metricDecorator->import($testResultReport);
    }

    public function testHandleCountersWhenTestResultReportIsFullyProcessed(): void
    {
        $decorated = $this->createMock(TestResultReportImportService::class);
        $decorated->expects($this->once())->method('import');

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureHistogram')
                ->with(Mockery::on(static function (ImportDuration $metric): bool {
                    return is_float($metric->getValue());
                }));
            $mock->expects('measureHistogram')
                ->with(Mockery::on(static function (ProcessingDuration $metric): bool {
                    return is_float($metric->getValue());
                }));
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (ImportStatus $metric): bool {
                    return $metric->getLabels() === ['status' => 'processed'];
                }));
        });

        $metricDecorator = $this->createMetricDecorator($decorated);
        $metricDecorator->import($testResultReport);
    }

    public function testHandleCountersTestReportingNotAllowedForOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $exception = new TestReportingNotAllowedForOrganisationException($organisation);

        $decorated = $this->createMock(TestResultReportImportService::class);
        $decorated->expects($this->once())->method('import')
            ->willThrowException($exception);

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());

        $this->mock(MetricRepository::class, static function (MockInterface $mock) use ($testResultReport): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (ReportingNotAllowedForOrganisation $metric) use ($testResultReport): bool {
                    return $metric->getLabels() === ['ggdIdentifier' => $testResultReport->ggdIdentifier];
                }));
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (ImportStatus $metric): bool {
                    return $metric->getLabels() === ['status' => 'processed'];
                }));
        });

        $this->expectException($exception::class);

        $metricDecorator = $this->createMetricDecorator($decorated);
        $metricDecorator->import($testResultReport);
    }

    public function createMetricDecorator(
        TestResultReportImportService $decorated,
    ): TestResultReportImportServiceMetricDecorator {
        return new TestResultReportImportServiceMetricDecorator(
            $decorated,
            new ProcessingDurationCalculator(),
            $this->app->get(MetricService::class),
        );
    }
}

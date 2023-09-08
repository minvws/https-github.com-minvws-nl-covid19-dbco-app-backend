<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Events\JobHandled;
use App\Jobs\ExportCaseToOsiris;
use App\Listeners\Osiris\JobHandledListener;
use App\Models\Metric\Osiris\CaseExportJobAttempts;
use App\Models\Metric\Osiris\CaseExportJobRun;
use App\Models\Metric\Osiris\CaseTestresultBoundToForwardedDuration;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use Illuminate\Contracts\Queue\Job;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use stdClass;
use Tests\Unit\UnitTestCase;

use function array_key_exists;

#[Group('osiris')]
final class JobHandledListenerTest extends UnitTestCase
{
    public function testCaseExportJobRunCounterNotIncrementedForNonExportCaseEvent(): void
    {
        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->never())
            ->method('measureCounter');
        $metricRepository->expects($this->never())
            ->method('measureGauge');
        $metricRepository->expects($this->never())
            ->method('measureHistogram');

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('resolveName')
            ->willReturn(stdClass::class);

        $event = new JobHandled($job, $this->faker->randomFloat(2, 1, 1000));
        $jobHandledListener = new JobHandledListener(new MetricService(new NullLogger(), $metricRepository));
        $jobHandledListener->handle($event);
    }

    public function testMeasureCaseExportJobRunMetricsOnFailure(): void
    {
        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('resolveName')
            ->willReturn(ExportCaseToOsiris::class);
        $job->expects($this->once())
            ->method('hasFailed')->willReturn(true);
        $job->expects($this->once())
            ->method('maxTries')
            ->willReturn(1);
        $job->expects($this->once())
            ->method('attempts')
            ->willReturn(1);

        $metricRepository = Mockery::mock(MetricRepository::class);

        $metricRepository->expects('measureCounter')
            ->with(Mockery::on(static function (CaseExportJobRun $metric): bool {
                $labels = $metric->getLabels();

                $hasStatusLabel = array_key_exists('status', $labels);
                $isStatusValid = $labels['status'] === 'failed';

                return $hasStatusLabel && $isStatusValid;
            }));

        $metricRepository->expects('measureHistogram')
            ->withArgs(static fn ($arg1) => $arg1 instanceof CaseTestresultBoundToForwardedDuration);

        $metricRepository->expects('measureHistogram')
            ->with(Mockery::on(static function (CaseExportJobAttempts $metric): bool {
                $labels = $metric->getLabels();

                $hasStatusLabel = array_key_exists('status', $labels);
                $isStatusValid = $labels['status'] === 'failed';

                return $hasStatusLabel && $isStatusValid;
            }));

        $metricService = new MetricService(new NullLogger(), $metricRepository);

        $jobHandledListener = new JobHandledListener($metricService);
        $jobHandledListener->handle(new JobHandled($job, $this->faker->randomFloat(2, 1, 1000)));
    }

    public function testMeasureCaseExportJobRunMetricsOnSuccess(): void
    {
        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('resolveName')
            ->willReturn(ExportCaseToOsiris::class);
        $job->expects($this->once())
            ->method('hasFailed')
            ->willReturn(false);
        $job->expects($this->once())
            ->method('isReleased')
            ->willReturn(false);
        $job->expects($this->once())
            ->method('maxTries')
            ->willReturn(1);
        $job->expects($this->once())
            ->method('attempts')
            ->willReturn(1);

        $metricRepository = Mockery::mock(MetricRepository::class);

        $metricRepository->expects('measureCounter')
            ->with(Mockery::on(static function (CaseExportJobRun $metric): bool {
                $labels = $metric->getLabels();

                $hasStatusLabel = array_key_exists('status', $labels);
                $isStatusValid = $labels['status'] === 'success';

                return $hasStatusLabel && $isStatusValid;
            }));

        $metricRepository->expects('measureHistogram')
            ->withArgs(static fn ($arg1) => $arg1 instanceof CaseTestresultBoundToForwardedDuration);

        $metricRepository->expects('measureHistogram')
            ->with(Mockery::on(static function (CaseExportJobAttempts $metric): bool {
                $labels = $metric->getLabels();

                $hasStatusLabel = array_key_exists('status', $labels);
                $isStatusValid = $labels['status'] === 'success';

                return $hasStatusLabel && $isStatusValid;
            }));


        $metricService = new MetricService(new NullLogger(), $metricRepository);
        $jobHandledListener = new JobHandledListener($metricService);

        $jobHandledListener->handle(new JobHandled($job, $this->faker->randomFloat(2, 1, 1000)));
    }
}

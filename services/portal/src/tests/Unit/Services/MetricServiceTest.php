<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Metric\CircuitBreaker\Availability;
use App\Models\Metric\Osiris\CaseCreationToForwardingDuration;
use App\Models\Metric\TestResult\TestResultToCovidCaseAssignment;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Tests\Unit\UnitTestCase;

class MetricServiceTest extends UnitTestCase
{
    public function testRecordCounter(): void
    {
        $metric = TestResultToCovidCaseAssignment::newCase();

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureCounter')
            ->with($metric);

        $metricService = $this->getMetricService($metricRepository);
        $metricService->measure($metric);
    }

    public function testRecordGauge(): void
    {
        $metric = Availability::available($this->faker->word());

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureGauge')
            ->with($metric);

        $metricService = $this->getMetricService($metricRepository);
        $metricService->measure($metric);
    }

    public function testRecordHistogram(): void
    {
        $metric = new CaseCreationToForwardingDuration($this->faker->randomDigit());

        $metricRepository = $this->createMock(MetricRepository::class);
        $metricRepository->expects($this->once())
            ->method('measureHistogram')
            ->with($metric);

        $metricService = $this->getMetricService($metricRepository);
        $metricService->measure($metric);
    }

    public function getMetricService(
        MetricRepository|MockObject $metricRepository,
    ): MetricService {
        return new MetricService(new NullLogger(), $metricRepository);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Metric\CircuitBreaker\Availability;
use App\Models\Metric\Metric;
use App\Models\Metric\TestResult\ImportStatus;
use App\Models\Metric\TestResult\ProcessingDuration;
use App\Repositories\Metric\MetricRepository;
use App\Services\MetricService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\FeatureTestCase;
use UnexpectedValueException;

#[Group('metric')]
class MetricServiceTest extends FeatureTestCase
{
    public function testMeasureCounterMetric(): void
    {
        $metric = ImportStatus::processed();
        $this->measure($metric, 'measureCounter');
    }

    public function testMeasureGaugeMetric(): void
    {
        $metric = Availability::available($this->faker->word());
        $this->measure($metric, 'measureGauge');
    }

    public function testMeasureHistogramMetric(): void
    {
        $metric = new ProcessingDuration($this->faker->randomFloat());
        $this->measure($metric, 'measureHistogram');
    }

    public function testUnkownMetric(): void
    {
        /** @var MetricRepository|MockObject $metricRepository */
        $metricRepository = $this->mock(MetricRepository::class);

        $metric = new class implements Metric {
            public function getName(): string
            {
                return '';
            }

            public function getHelp(): string
            {
                return '';
            }

            public function getLabels(): array
            {
                return [];
            }
        };

        $metricService = $this->getMetricService($metricRepository);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('unknown metric type');
        $metricService->measure($metric);
    }

    private function measure(Metric $metric, string $expectedMethod): void
    {
        /** @var MetricRepository|MockObject $metricRepository */
        $metricRepository = $this->mock(MetricRepository::class, static function (MockInterface $mock) use (
            $expectedMethod,
            $metric,
        ): void {
            $mock->expects($expectedMethod)->with($metric);
        });

        $metricService = $this->getMetricService($metricRepository);
        $metricService->measure($metric);
    }

    public function getMetricService(
        MockObject|MetricRepository $metricRepository,
    ): MetricService {
        return $this->app->make(MetricService::class, ['metricRepository' => $metricRepository]);
    }
}

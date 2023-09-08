<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Metric;

use App\Models\Metric\Osiris\CaseExportJobAttempts;
use App\Models\Metric\RateLimiter\RateLimiterHit;
use App\Models\Metric\TestResult\ReportingNotAllowedForOrganisation;
use App\Repositories\Metric\PrometheusRepository;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use PHPUnit\Framework\MockObject\MockObject;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function array_values;

class PrometheusRepositoryTest extends UnitTestCase
{
    public function testRecordCounter(): void
    {
        $ggdIdentifier = $this->faker->word();
        $metric = new ReportingNotAllowedForOrganisation($ggdIdentifier);

        /** @var Counter|MockObject $counter */
        $counter = $this->createMock(Counter::class);
        $counter->expects($this->once())
            ->method('inc')
            ->with([$ggdIdentifier]);

        /** @var PrometheusExporter|MockObject $prometheusExporter */
        $prometheusExporter = $this->createMock(PrometheusExporter::class);
        $prometheusExporter->expects($this->once())
            ->method('getOrRegisterCounter')
            ->with($metric->getName(), $metric->getHelp(), ['ggdIdentifier'])
            ->willReturn($counter);

        $prometheusRepository = new PrometheusRepository($prometheusExporter);
        $prometheusRepository->measureCounter($metric);
    }

    public function testRecordGauge(): void
    {
        $metric = new RateLimiterHit((float) $this->faker->numberBetween(0, 150), $this->faker->word());

        /** @var Gauge|MockObject $gauge */
        $gauge = $this->createMock(Gauge::class);
        $gauge->expects($this->once())
            ->method('set')
            ->with($metric->getValue(), array_values($metric->getLabels()));

        /** @var PrometheusExporter|MockObject $prometheusExporter */
        $prometheusExporter = $this->createMock(PrometheusExporter::class);
        $prometheusExporter->expects($this->once())
            ->method('getOrRegisterGauge')
            ->with($metric->getName(), $metric->getHelp(), array_keys($metric->getLabels()))
            ->willReturn($gauge);

        $prometheusRepository = new PrometheusRepository($prometheusExporter);
        $prometheusRepository->measureGauge($metric);
    }

    public function testRecordHistogram(): void
    {
        $metric = CaseExportJobAttempts::success($this->faker->randomDigit(), $this->faker->numberBetween(2, 9));

        /** @var Histogram|MockObject $histogram */
        $histogram = $this->createMock(Histogram::class);
        $histogram->expects($this->once())
            ->method('observe')
            ->with($metric->getValue(), $metric->getLabels());

        /** @var PrometheusExporter|MockObject $prometheusExporter */
        $prometheusExporter = $this->createMock(PrometheusExporter::class);
        $prometheusExporter->expects($this->once())
            ->method('getOrRegisterHistogram')
            ->with($metric->getName(), $metric->getHelp(), ['status'], $metric->getBuckets())
            ->willReturn($histogram);

        $prometheusRepository = new PrometheusRepository($prometheusExporter);
        $prometheusRepository->measureHistogram($metric);
    }
}
